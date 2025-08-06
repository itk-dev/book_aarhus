<?php

namespace App\MessageHandler;

use App\Interface\UserBookingCacheServiceInterface;
use App\Message\UpdateBookingInCacheMessage;
use App\Service\MetricsHelper;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class UpdateBookingInCacheHandler
{
    public function __construct(
        private readonly UserBookingCacheServiceInterface $userBookingCacheService,
        private readonly MetricsHelper $metricsHelper,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function __invoke(UpdateBookingInCacheMessage $message): void
    {
        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::INVOKE);

        $this->userBookingCacheService->changeCacheEntry($message->getExchangeId(), $message->getChanges());

        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::COMPLETE);
    }
}
