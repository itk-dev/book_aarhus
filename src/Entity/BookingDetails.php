<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;

class BookingDetails
{
    #[ApiProperty(identifier: true)]
    public string $id;

    public string $displayName;

    public string $body;
}
