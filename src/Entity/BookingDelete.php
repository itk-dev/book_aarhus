<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;

class BookingDelete
{
    // TODO: Remove.

    #[ApiProperty(identifier: true)]
    public string $status;
}
