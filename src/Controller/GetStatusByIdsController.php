<?php

namespace App\Controller;

use App\Enum\UserBookingStatusEnum;
use App\Service\BookingServiceInterface;
use App\Service\Metric;
use App\Service\UserBookingCacheServiceInterface;
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
        private readonly Metric $metric,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $this->metric->incFunctionTotal($this, __FUNCTION__, Metric::INVOKE);

        $exchangeIds = json_decode($request->getContent())->ids;

        if (empty($exchangeIds)) {
            $this->metric->incExceptionTotal(NotFoundHttpException::class);
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
                $this->metric->incExceptionTotal(\Exception::class);

                $statuses[] = [
                    'exchangeId' => $id,
                    'status' => null,
                ];
            }
        }

        $this->metric->incFunctionTotal($this, __FUNCTION__, Metric::COMPLETE);

        return new JsonResponse($statuses);
    }
}
