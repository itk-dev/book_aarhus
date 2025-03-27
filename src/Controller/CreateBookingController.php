<?php

namespace App\Controller;

use ApiPlatform\Metadata\Exception\InvalidArgumentException;
use App\Entity\Main\ApiKeyUser;
use App\Entity\Main\Booking;
use App\Entity\Resources\AAKResource;
use App\Enum\UserBookingStatusEnum;
use App\Exception\ResourceNotFoundException;
use App\Repository\Resources\AAKResourceRepository;
use App\Security\Voter\BookingVoter;
use App\Service\BookingServiceInterface;
use App\Service\CreateBookingService;
use App\Service\MetricsHelper;
use App\Service\UserBookingCacheServiceInterface;
use App\Utils\ValidationUtilsInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class CreateBookingController extends AbstractController
{
    public function __construct(
        private readonly MetricsHelper $metricsHelper,
        private readonly CreateBookingService $createBookingService,
        private readonly ValidationUtilsInterface $validationUtils,
        private readonly AAKResourceRepository $aakResourceRepository,
        private readonly BookingServiceInterface $bookingService,
        private readonly UserBookingCacheServiceInterface $userBookingCacheService,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        // Algorithm:
        // Validate input.
        // Check for free intervals.
        // Create all bookings.
        // Check if any bookings failed and potential cleanup.

        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::INVOKE);

        $user = $this->getUser();
        if ($user instanceof ApiKeyUser) {
            $userId = $user->getId();
        }

        $content = $request->toArray();

        $abortIfAnyFail = $content['abortIfAnyFail'] ?? false;

        if (empty($content['bookings'])) {
            throw new InvalidArgumentException('No bookings provided.');
        }

        // Validate inputs.
        $bookings = [];

        try {
            foreach ($content['bookings'] as $item) {
                $email = $this->validationUtils->validateEmail($item['resourceId']);

                /** @var AAKResource $resource */
                $resource = $this->aakResourceRepository->findOneBy(['resourceMail' => $email]);

                if (is_null($resource)) {
                    throw new ResourceNotFoundException('Resource does not exist', 404);
                }

                $body = $this->createBookingService->composeBookingContents($item, $resource, $item['metaData'] ?? []);
                $htmlContents = $this->createBookingService->renderContentsAsHtml($body);

                $booking = new Booking();
                $booking->setBody($htmlContents);
                $booking->setUserName($item['name'] ?? '');
                $booking->setUserMail($item['email'] ?? '');
                $booking->setMetaData($item['metaData'] ?? []);
                $booking->setSubject($item['subject'] ?? '');
                $booking->setResourceEmail($email);
                $booking->setResourceName($resource->getResourceName());
                $booking->setStartTime($this->validationUtils->validateDate($item['start']));
                $booking->setEndTime($this->validationUtils->validateDate($item['end']));
                $booking->setUserId($userId ?? '');
                $booking->setUserPermission($item['userPermission'] ?? BookingVoter::PERMISSION_CITIZEN);
                $booking->setId($item['id']);

                $bookings[] = $booking;
            }
        } catch (\Throwable $e) {
            $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::EXCEPTION);
            throw $e;
        }

        // Check for free intervals
        try {
            foreach ($bookings as $booking) {

                $resourceEmail = $resource->getResourceMail();
                $result = $this->bookingService->getBusyIntervals([$resourceEmail], $booking->getStartTime(), $booking->getEndTime());

                // TODO: What should we respond here?
                if (!empty($result[$resourceEmail]) && !$resource->getAcceptConflict()) {
                    throw new \Exception("Resource is busy in the desired timeslot");
                }
            }
        } catch (\Throwable $e) {
            $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::EXCEPTION);
            throw $e;
        }

        // Create bookings, previous steps went well so just get to it.
        $createdBookings = [];
        $hasAnyBookingsFailed = FALSE;

        try {
            foreach ($bookings as $booking) {
                $createdBooking = $this->createBookingService->createBooking($booking);

                if (!in_array($createdBooking['status'], [UserBookingStatusEnum::ACCEPTED->name, UserBookingStatusEnum::AWAITING_APPROVAL->name])) {
                    $hasAnyBookingsFailed = TRUE;
                }

                $createdBookings[] = $createdBooking;
            }
        } catch (\Throwable $e) {
            $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::EXCEPTION);
            throw $e;
        }

        // Cancel if necessary.
        try {
            if ($abortIfAnyFail && $hasAnyBookingsFailed) {
                foreach ($createdBookings as $createdBooking) {
                    if (!in_array($createdBooking['status'], [UserBookingStatusEnum::ACCEPTED->name, UserBookingStatusEnum::AWAITING_APPROVAL->name])) {
                        // Prepare exchange id for cache deletion before deleting booking.
                        $exchangeId = $this->bookingService->getBookingIdFromICalUid($createdBooking['iCalUid']);
                        // Delete booking
                        $this->bookingService->deleteBookingByICalUid($createdBooking['iCalUid']);
                        // Remove from cache
                        $this->userBookingCacheService->deleteCacheEntry($exchangeId);

                        // TODO: How do we explain that this booking would have gone well but was cancelled?
                        $createdBooking['status'] = UserBookingStatusEnum::NONE->name;
                    }
                }
            }
        } catch (\Throwable $e) {
            // TODO: Can we do some sort of cleanup here?
            // A created booking might have been created when it should not have been.
            $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::EXCEPTION);
            throw $e;
        }

        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::COMPLETE);

        return new Response(json_encode(['bookings' => $createdBookings]), $hasAnyBookingsFailed ? 400 : 201);
    }
}
