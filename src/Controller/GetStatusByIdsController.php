<?php

namespace App\Controller;

use App\Enum\UserBookingStatusEnum;
use App\Interface\BookingServiceInterface;
use App\Interface\UserBookingCacheServiceInterface;
use App\Service\MetricsHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[AsController]
class GetStatusByIdsController extends AbstractController
{
    public function __construct(
        private readonly BookingServiceInterface $bookingService,
        private readonly UserBookingCacheServiceInterface $userBookingCacheService,
        private readonly MetricsHelper $metricsHelper,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::INVOKE);

        $exchangeIds = json_decode($request->getContent())->ids;

        if (empty($exchangeIds)) {
            $this->metricsHelper->incExceptionTotal(NotFoundHttpException::class);
            throw new NotFoundHttpException('Resource not found');
        }

        $statuses = [];

        foreach ($exchangeIds as $id) {
            try {
                $booking = $this->bookingService->getBooking($id);
                $userBooking = $this->bookingService->getUserBookingFromApiData($booking);

                $status = in_array($userBooking->status, [
                    UserBookingStatusEnum::ACCEPTED->name,
                    UserBookingStatusEnum::DECLINED->name,
                ]) ? $userBooking->status : UserBookingStatusEnum::AWAITING_APPROVAL->name;

                $statuses[] = [
                    'exchangeId' => $id,
                    'status' => $status,
                ];

                // Update booking cache status if accepted or declined.
                // Possible event statuses: https://learn.microsoft.com/en-us/graph/api/resources/responsestatus?view=graph-rest-1.0#properties
                if ($status != UserBookingStatusEnum::AWAITING_APPROVAL->name) {
                    $this->userBookingCacheService->changeCacheEntry($id, ['status' => $userBooking->status]);
                }
            } catch (\Exception) {
                $this->metricsHelper->incExceptionTotal(\Exception::class);

                $statuses[] = [
                    'exchangeId' => $id,
                    'status' => null,
                ];
            }
        }

        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::COMPLETE);

        return new JsonResponse($statuses);
    }
}
