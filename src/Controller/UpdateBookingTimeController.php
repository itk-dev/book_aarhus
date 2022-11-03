<?php

namespace App\Controller;

use App\Entity\Main\UserBooking;
use App\Service\BookingServiceInterface;
use DateTime;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class UpdateBookingTimeController extends AbstractController
{
    public function __construct(
        private readonly BookingServiceInterface $bookingService,
    ) {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function __invoke(Request $request): Response
    {
        $p = $request->toArray();

        /** @var UserBooking $userBooking */
        $userBookingData = $this->bookingService->getBooking($p['id']);
        $userBooking = $this->bookingService->getUserBookingFromApiData($userBookingData);

        $userBooking->start = new DateTime($p['start']);
        $userBooking->end = new DateTime($p['end']);

        $b = $this->bookingService->updateBooking($userBooking);

        $a = 1;

        return new JsonResponse([], 201);
    }
}
