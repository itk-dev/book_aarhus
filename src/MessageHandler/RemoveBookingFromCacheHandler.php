<?php

namespace App\MessageHandler;

use App\Interface\UserBookingCacheServiceInterface;
use App\Message\RemoveBookingFromCacheMessage;
use App\Service\MetricsHelper;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class RemoveBookingFromCacheHandler
{
    public function __construct(
        private readonly UserBookingCacheServiceInterface $userBookingCacheService,
        private readonly MetricsHelper $metricsHelper,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function __invoke(RemoveBookingFromCacheMessage $message): void
    {
        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::INVOKE);

        $this->userBookingCacheService->deleteCacheEntry($message->getExchangeId());

        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::COMPLETE);
    }
}
