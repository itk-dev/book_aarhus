<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;

class UserBooking
{
    #[ApiProperty(identifier: true)]
    public string $id;

    public string $hitId;

    public string $summary;

    public string $subject;

    public ?\DateTimeInterface $start;

    public ?\DateTimeInterface $end;
}
