<?php

namespace App\DataPersister;

use Psr\Log\LoggerInterface;
use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;

final class UserBookingDataPersister implements ContextAwareDataPersisterInterface
{
    public function __construct(private LoggerInterface $logger)
    {
        
    }
    public function supports($data, array $context = []): bool
    {
        $this->logger->info("supports");
        return true;
    }

    public function persist($data, array $context = [])
    {
        // call your persistence layer to save $data
        $this->logger->info("supports");
        return $data;
    }

    public function remove($data, array $context = [])
    {
        $this->logger->info("supports");
        // call your persistence layer to delete $data
    }
}