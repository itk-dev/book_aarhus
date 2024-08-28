<?php

namespace App\MessageHandler;

use App\Message\RemoveBookingFromCacheMessage;
use App\Service\Metric;
use App\Service\UserBookingCacheServiceInterface;
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
        $this->metric->incFunctionTotal($this, __FUNCTION__, Metric::INVOKE);

        $this->userBookingCacheService->deleteCacheEntry($message->getExchangeId());
        $this->metric->totalIncByOne('cache_entry_deleted', 'Cache entry has been deleted.', $this, ['complete']);
    }
}
