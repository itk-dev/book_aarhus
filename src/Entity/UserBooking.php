<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;

class UserBooking
{
    #[ApiProperty(identifier: true)]
    public string $id;

    public string $hitId;

    public ?\DateTimeInterface $start;
    
    public ?\DateTimeInterface $end;
}
