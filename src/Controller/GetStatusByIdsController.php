<?php

namespace App\Controller;

use App\Enum\UserBookingStatusEnum;
use App\Service\BookingServiceInterface;
use App\Service\UserBookingCacheServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\SerializerInterface;

#[AsController]
class GetStatusByIdsController extends AbstractController
{
    public function __construct(
        private readonly BookingServiceInterface $bookingService,
        private readonly SerializerInterface $serializer,
        private readonly UserBookingCacheServiceInterface $userBookingCacheService,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $exchangeIds = json_decode($request->getContent())->ids;
        if (empty($exchangeIds)) {
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
                $statuses[] = [
                    'exchangeId' => $id,
                    'status' => null,
                ];
            }
        }

        $data = $this->serializer->serialize($statuses, 'json', ['groups' => 'resource']);

        return new Response($data, 200);
    }
}
