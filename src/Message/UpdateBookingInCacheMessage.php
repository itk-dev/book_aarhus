<?php

namespace App\Message;

class UpdateBookingInCacheMessage
{
    public function __construct(
        private readonly string $exchangeId,
        private readonly array $changes,
    ) {
    }

    public function getExchangeId(): string
    {
        return $this->exchangeId;
    }

    public function getChanges(): array
    {
        return $this->changes;
    }
}
