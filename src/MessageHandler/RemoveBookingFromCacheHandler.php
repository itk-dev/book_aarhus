<?php

namespace App\MessageHandler;

use App\Message\RemoveBookingFromCacheMessage;
use App\Service\Metric;
use App\Service\UserBookingCacheServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class RemoveBookingFromCacheHandler
{
    public function __construct(
        private readonly UserBookingCacheServiceInterface $userBookingCacheService,
        private readonly Metric $metric,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function __invoke(RemoveBookingFromCacheMessage $message): void
    {
        $this->metric->counter('invoke', null, $this);
        $this->userBookingCacheService->deleteCacheEntry($message->getExchangeId());
        $this->metric->counter('cacheEntryDeleted', "Cache entry has been deleted.", $this);
    }
}
