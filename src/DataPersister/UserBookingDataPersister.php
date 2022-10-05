<?php

namespace App\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Entity\Main\UserBooking;

class UserBookingDataPersister implements ContextAwareDataPersisterInterface
{
    public function supports($data, array $context = []): bool
    {
        return $data instanceof UserBooking;
    }

    public function remove($data, array $context = [])
    {
        // TODO: Call Microsoft Graph to delete $data
        $p = 1;
    }

    public function persist($data, array $context = [])
    {
        $p = 2;
        // TODO: Implement edit booking.
        return $data;
    }
}
