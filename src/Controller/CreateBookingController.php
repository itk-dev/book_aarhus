<?php

namespace App\Controller;

use ApiPlatform\Metadata\Exception\InvalidArgumentException;
use App\Entity\Main\ApiKeyUser;
use App\Entity\Main\Booking;
use App\Entity\Main\Resource;
use App\Enum\CreateBookingStatusEnum;
use App\Enum\UserBookingStatusEnum;
use App\Interface\BookingServiceInterface;
use App\Interface\UserBookingCacheServiceInterface;
use App\Interface\ValidationUtilsInterface;
use App\Model\BookingRequest;
use App\Repository\ResourceRepository;
use App\Security\Voter\BookingVoter;
use App\Service\CreateBookingService;
use App\Service\MetricsHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

#[AsController]
class CreateBookingController extends AbstractController
{
    public function __construct(
        private readonly MetricsHelper $metricsHelper,
        private readonly CreateBookingService $createBookingService,
        private readonly ValidationUtilsInterface $validationUtils,
        private readonly ResourceRepository $aakResourceRepository,
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
        $bookingRequests = [];

        foreach ($content['bookings'] as $input) {
            $bookingRequest = new BookingRequest($input, CreateBookingStatusEnum::REQUEST);

            try {
                $email = $this->validationUtils->validateEmail($input['resourceId']);
            } catch (InvalidArgumentException) {
                $bookingRequest->status = CreateBookingStatusEnum::INVALID;
                continue;
            }

            /** @var Resource $resource */
            $resource = $this->aakResourceRepository->findOneBy(['resourceMail' => $email]);

            if (null === $resource) {
                $bookingRequest->status = CreateBookingStatusEnum::ERROR;
            } else {
                $bookingRequest->resource = $resource;

                try {
                    $body = $this->createBookingService->composeBookingContents($input, $resource, $input['metaData'] ?? []);
                    $htmlContents = $this->createBookingService->renderContentsAsHtml($body);

                    $booking = new Booking();
                    $booking->setBody($htmlContents);
                    $booking->setUserName($input['name'] ?? '');
                    $booking->setUserMail($input['email'] ?? '');
                    $booking->setMetaData($input['metaData'] ?? []);
                    $booking->setSubject($input['subject'] ?? '');
                    $booking->setResourceEmail($email);
                    $booking->setResourceName($resource->getResourceName());
                    $booking->setStartTime($this->validationUtils->validateDate($input['start']));
                    $booking->setEndTime($this->validationUtils->validateDate($input['end']));
                    $booking->setUserId($userId ?? '');
                    $booking->setUserPermission($input['userPermission'] ?? BookingVoter::PERMISSION_CITIZEN);
                    $booking->setId($input['id']);

                    $bookingRequest->booking = $booking;
                } catch (\Exception) {
                    $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::EXCEPTION);

                    $bookingRequest->status = CreateBookingStatusEnum::ERROR;

                    if ($abortIfAnyFail) {
                        throw new BadRequestHttpException('Error validating booking. Aborting.');
                    }
                }
            }

            $bookingRequests[] = $bookingRequest;
        }

        // Check for free intervals
        foreach ($bookingRequests as $bookingRequest) {
            if (CreateBookingStatusEnum::REQUEST === $bookingRequest->status) {
                $booking = $bookingRequest->booking;
                $resource = $bookingRequest->resource;

                if (null === $booking || null === $resource) {
                    continue;
                }

                $resourceEmail = $booking->getResourceEmail();

                try {
                    $result = $this->bookingService->getBusyIntervals([$resourceEmail], $booking->getStartTime(), $booking->getEndTime());
                } catch (\Exception) {
                    $bookingRequest->status = CreateBookingStatusEnum::ERROR;

                    if ($abortIfAnyFail) {
                        throw new HttpException(500, 'Error checking booking interval. Aborting.');
                    }
                }

                if (!empty($result[$resourceEmail]) && !$resource->getAcceptConflict()) {
                    $bookingRequest->status = CreateBookingStatusEnum::CONFLICT;

                    if ($abortIfAnyFail) {
                        throw new BadRequestHttpException('Error validating booking. Aborting.');
                    }
                }
            }
        }

        $hasAnyBookingsFailed = false;

        // Create bookings.
        foreach ($bookingRequests as $bookingRequest) {
            if (CreateBookingStatusEnum::REQUEST === $bookingRequest->status) {
                $booking = $bookingRequest->booking;

                if (null === $booking) {
                    continue;
                }

                $createdBooking = $this->createBookingService->createBooking($booking);

                if (!in_array($createdBooking['status'], [UserBookingStatusEnum::ACCEPTED->name, UserBookingStatusEnum::AWAITING_APPROVAL->name])) {
                    $hasAnyBookingsFailed = true;
                    $bookingRequest->status = CreateBookingStatusEnum::ERROR;
                } else {
                    $bookingRequest->status = CreateBookingStatusEnum::SUCCESS;

                    $bookingRequest->createdBooking = $createdBooking;
                }
            }
        }

        // Cancel if necessary.
        if ($abortIfAnyFail && $hasAnyBookingsFailed) {
            foreach ($bookingRequests as $bookingRequest) {
                if (CreateBookingStatusEnum::SUCCESS === $bookingRequest->status) {
                    $createdBooking = $bookingRequest->createdBooking;

                    if (null !== $createdBooking) {
                        $iCalUId = $createdBooking['iCalUid'];

                        try {
                            // Delete booking
                            $this->bookingService->deleteBookingByICalUid($iCalUId);
                            // Remove from cache
                            $this->userBookingCacheService->deleteCacheEntry($iCalUId);

                            $bookingRequest->status = CreateBookingStatusEnum::CANCELLED;
                        } catch (\Throwable) {
                            $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::EXCEPTION);

                            // In this case the booking still exists. Therefore, it remains in SUCCESS status.
                        }
                    }
                }
            }
        }

        $bookingResults = array_map(fn(BookingRequest $bookingRequest) => [
            'input' => $bookingRequest->input,
            'status' => $bookingRequest->status->value,
            'createdBooking' => $bookingRequest->createdBooking,
        ], $bookingRequests);

        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::COMPLETE);

        return new JsonResponse(['bookings' => $bookingResults], \Symfony\Component\HttpFoundation\Response::HTTP_OK);
    }
}
