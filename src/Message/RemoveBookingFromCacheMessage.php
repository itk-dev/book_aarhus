<?php

namespace App\Message;

class RemoveBookingFromCacheMessage
{
    public function __construct(private readonly string $exchangeId)
    {
    }

    public function getExchangeId(): string
    {
        return $this->exchangeId;
    }
}
