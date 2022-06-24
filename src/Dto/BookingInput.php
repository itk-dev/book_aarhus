<?php

namespace App\Dto;

final class BookingInput
{
    public string $resourceEmail;
    public string $resourceName;
    public string $subject;
    public string $body;
    public string $startTime;
    public string $endTime;
}
