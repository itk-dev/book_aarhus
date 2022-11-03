<?php

namespace App\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Entity\Main\UserBooking;
use App\Exception\MicrosoftGraphCommunicationException;
use App\Exception\UserBookingException;
use App\Service\BookingServiceInterface;
use Psr\Log\LoggerInterface;

class UserBookingDataPersister implements ContextAwareDataPersisterInterface
{
    public function __construct(
        private readonly BookingServiceInterface $bookingService,
        private readonly LoggerInterface $logger,
    ) {
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
     * @throws UserBookingException
     */
    public function persist($data, array $context = [])
    {
        $b = $this->bookingService->updateBooking($data);

        $p = 1;
        $this->logger->info('HERE!!!!');

        return $data;
    }
}
