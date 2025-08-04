<?php

namespace App\Controller;

use App\Enum\CancelBookingStatusEnum;
use App\Exception\UserBookingException;
use App\Interface\BookingServiceInterface;
use App\Interface\UserBookingCacheServiceInterface;
use App\Security\Voter\UserBookingVoter;
use App\Service\MetricsHelper;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class CancelBookingController extends AbstractController
{
    public function __construct(
        private readonly MetricsHelper $metricsHelper,
        private readonly BookingServiceInterface $bookingService,
        private readonly Security $security,
        private readonly LoggerInterface $logger,
        private readonly UserBookingCacheServiceInterface $userBookingCacheService,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        // Algorithm:
        // Validate input.
        // For each booking
        // - Check that it exists, otherwise report NOT_FOUND.
        // - Check that the user is allowed to delete the booking.
        // - Delete the booking.
        // - Remove the booking from the user booking cache.
        // Respond with status for each id.

        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::INVOKE);

        $content = $request->toArray();

        $ids = $content['ids'] ?? [];

        $results = [];

        foreach ($ids as $iCalUID) {
            $result = [
                'id' => $iCalUID,
                'status' => CancelBookingStatusEnum::UNRESOLVED->value,
            ];

            try {
                $id = $this->bookingService->getBookingIdFromICalUid($iCalUID);

                // - Check that it exists, otherwise report NOT_FOUND.
                if (null === $id) {
                    throw new UserBookingException('Booking not found', 404);
                }

                $bookingData = $this->bookingService->getBooking($id);
                $userBooking = $this->bookingService->getUserBookingFromApiData($bookingData);

                // - Check that the user is allowed to delete the booking.
                if (!$this->security->isGranted(UserBookingVoter::DELETE, $userBooking)) {
                    $this->logger->error('User does not have permission to delete the given booking.');

                    $result['status'] = CancelBookingStatusEnum::FORBIDDEN->value;
                } else {
                    $this->bookingService->deleteBooking($userBooking);
                    $this->userBookingCacheService->deleteCacheEntryByICalUId($iCalUID);

                    $result['status'] = CancelBookingStatusEnum::DELETED->value;
                }
            } catch (UserBookingException $e) {
                if (404 == $e->getCode()) {
                    $result['status'] = CancelBookingStatusEnum::NOT_FOUND->value;
                } else {
                    $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::EXCEPTION);
                    $result['status'] = CancelBookingStatusEnum::ERROR->value;
                }
            } catch (\Throwable) {
                $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::EXCEPTION);
                $result['status'] = CancelBookingStatusEnum::ERROR->value;
            } finally {
                $results[] = $result;
            }
        }

        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::COMPLETE);

        return new JsonResponse($results, \Symfony\Component\HttpFoundation\Response::HTTP_OK);
    }
}
