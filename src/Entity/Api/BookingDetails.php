<?php

namespace App\Entity\Api;

use ApiPlatform\Metadata\ApiProperty;

class BookingDetails
{
    #[ApiProperty(identifier: true)]
    public string $id;

    public string $displayName;

    public string $body;
}
