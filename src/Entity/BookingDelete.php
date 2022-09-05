<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;

class BookingDelete
{
    #[ApiProperty(identifier: true)]
    public string $status;
}
