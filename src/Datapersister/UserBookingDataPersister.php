<?php


namespace App\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Entity\Main\UserBooking;

final class BlogPostDataPersister implements ContextAwareDataPersisterInterface
{
    public function supports($data, array $context = []): bool
    {
        die('F 1');
        return $data instanceof UserBooking;
    }

    public function persist($data, array $context = [])
    {
        die('F 2');
        // call your persistence layer to save $data
        return $data;
    }

    public function remove($data, array $context = [])
    {
        die('F 3');
        // call your persistence layer to delete $data
    }
}