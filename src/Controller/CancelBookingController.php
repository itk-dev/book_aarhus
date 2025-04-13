<?php

namespace App\Controller;

use ApiPlatform\Metadata\Exception\InvalidArgumentException;
use App\Service\BookingServiceInterface;
use App\Service\MetricsHelper;
use App\Service\UserBookingCacheServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class CancelBookingController extends AbstractController
{
    public function __construct(
        private readonly MetricsHelper $metricsHelper,
        private readonly BookingServiceInterface $bookingService,
        private readonly UserBookingCacheServiceInterface $userBookingCacheService,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        // Algorithm:
        // Validate input.
        // Cancel bookings.

        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::INVOKE);

        $content = $request->toArray();

        $ids = $content['ids'] ?? false;

        if (empty($ids)) {
            throw new InvalidArgumentException('No ids provided.');
        }

        try {
            foreach ($ids as $id) {
                // TODO: Should we validate that user is allowed to delete booking?
                $this->bookingService->deleteBookingByICalUid($id);
                $this->userBookingCacheService->deleteCacheEntryByICalUId($id);
            }
        } catch (\Throwable $e) {
            $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::EXCEPTION);
            // TODO: Should we respond which cancellation has failed?
            throw $e;
        }

        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::COMPLETE);

        return new Response(null, 200);
    }
}
