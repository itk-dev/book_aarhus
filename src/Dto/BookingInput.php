<?php

namespace App\Dto;

final class BookingInput
{
    public string $resourceEmail;
    public string $subject;
    public string $body;
    public string $startTime;
    public string $endTime;
    public string $userId;
}
