<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use Symfony\Component\Uid\Ulid;

class Booking
{
    #[ApiProperty(identifier: true)]
    public string $id;

    public function __construct()
    {
        $this->id = Ulid::generate();
    }
}
