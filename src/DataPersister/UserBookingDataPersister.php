<?php

namespace App\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Entity\Main\UserBooking;
use App\Exception\MicrosoftGraphCommunicationException;
use App\Service\MicrosoftGraphServiceInterface;

class UserBookingDataPersister implements ContextAwareDataPersisterInterface
{
    public function __construct(private readonly MicrosoftGraphServiceInterface $microsoftGraphService)
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
            $this->microsoftGraphService->deleteBooking($data);
        }
    }

    /**
     * @throws MicrosoftGraphCommunicationException
     */
    public function persist($data, array $context = [])
    {
        if ($data instanceof UserBooking) {
            $this->microsoftGraphService->updateBooking($data);
        }

        return $data;
    }
}
