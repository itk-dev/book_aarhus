<?php

namespace App\MessageHandler;

use App\Message\UpdateBookingInCacheMessage;
use App\Service\Metric;
use App\Service\UserBookingCacheServiceInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class UpdateBookingInCacheHandler
{
    public function __construct(
        private readonly UserBookingCacheServiceInterface $userBookingCacheService,
        private readonly Metric $metric,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function __invoke(UpdateBookingInCacheMessage $message): void
    {
        $this->metric->incFunctionTotal($this, __FUNCTION__, Metric::INVOKE);

        $this->userBookingCacheService->changeCacheEntry($message->getExchangeId(), $message->getChanges());

        $this->metric->incFunctionTotal($this, __FUNCTION__, Metric::COMPLETE);
    }
}
