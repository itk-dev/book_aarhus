<?php

namespace App\Entity\Main;

use ApiPlatform\Core\Annotation\ApiProperty;

class UserBooking
{
    #[ApiProperty(identifier: true)]
    public string $id;

    public string $hitId;

    public string $iCalUId;

    public string $summary;

    public string $subject;

    public string $displayName;

    public string $body;

    public string $status;

    public ?\DateTimeInterface $start;

    public ?\DateTimeInterface $end;
}
