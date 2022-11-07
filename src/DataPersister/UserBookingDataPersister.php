<?php

namespace App\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Entity\Main\UserBooking;
use App\Exception\MicrosoftGraphCommunicationException;
use App\Exception\UserBookingException;
use App\Service\BookingServiceInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UserBookingDataPersister implements ContextAwareDataPersisterInterface
{
    public function __construct(
        private readonly BookingServiceInterface $bookingService,
    ) {
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof UserBooking;
    }

    public function remove($data, array $context = [])
    {
        try {
            if ($data instanceof UserBooking) {
                $this->bookingService->deleteBooking($data);
            }
        } catch (MicrosoftGraphCommunicationException $e) {
            throw new HttpException($e->getCode(), 'Booking could not be deleted.');
        }
    }

    public function persist($data, array $context = [])
    {
        try {
            if ($data instanceof UserBooking) {
                $this->bookingService->updateBooking($data);
            }
        } catch (MicrosoftGraphCommunicationException|UserBookingException $e) {
            throw new HttpException($e->getCode(), 'Booking could not be updated.');
        }

        return $data;
    }
}
