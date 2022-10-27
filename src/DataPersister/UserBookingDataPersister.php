<?php

namespace App\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Entity\Main\UserBooking;
use App\Exception\MicrosoftGraphCommunicationException;
use App\Service\BookingServiceInterface;

class UserBookingDataPersister implements ContextAwareDataPersisterInterface
{
    public function __construct(private readonly BookingServiceInterface $bookingService)
    {
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof UserBooking;
    }

    /**
     * @throws MicrosoftGraphCommunicationException
     */
    public function remove($data, array $context = [])
    {
        if ($data instanceof UserBooking) {
            $this->bookingService->deleteBooking($data);
        }
    }

    /**
     * @throws MicrosoftGraphCommunicationException
     */
    public function persist($data, array $context = [])
    {
        if ($data instanceof UserBooking) {
            $this->bookingService->updateBooking($data);
        }

        return $data;
    }
}
