<?php

namespace App\Controller;

use App\Exception\MicrosoftGraphCommunicationException;
use App\Service\BookingServiceInterface;
use App\Service\UserBookingCacheServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\HttpException;
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
          throw new HttpException(404, 'Resource not found');
      }

      $statuses = [];

      foreach ($exchangeIds as $id) {
          try {
            $booking = $this->bookingService->getBooking($id);
            $userBooking = $this->bookingService->getUserBookingFromApiData($booking);
            $statuses[] = [
              'exchangeId' => $id,
              'status' => $userBooking->status,
            ];

            // Update booking cache status.
            $this->userBookingCacheService->changeCacheEntry($id, ['status' => $userBooking->status]);
          } catch (\Exception $e) {
              $statuses[] = [
                  'exchangeId' => $id,
                  'status' => NULL,
              ];
          }
      }

      $data = $this->serializer->serialize($statuses, 'json', ['groups' => 'resource']);

      return new Response($data, 200);
  }
}
