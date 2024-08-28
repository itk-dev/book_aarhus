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
        $this->metric->incMethodTotal(__METHOD__, Metric::INVOKE);

        $this->userBookingCacheService->deleteCacheEntry($message->getExchangeId());

        $this->metric->incMethodTotal(__METHOD__, Metric::COMPLETE);
    }
}
