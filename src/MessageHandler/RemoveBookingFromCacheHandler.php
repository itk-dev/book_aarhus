<?php

namespace App\MessageHandler;

use App\Message\RemoveBookingFromCacheMessage;
use App\Service\UserBookingCacheServiceInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class RemoveBookingFromCacheHandler
{
    public function __construct(
        private readonly UserBookingCacheServiceInterface $userBookingCacheService,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function __invoke(RemoveBookingFromCacheMessage $message): void
    {
        $this->userBookingCacheService->deleteCacheEntry($message->getExchangeId());
    }
}
