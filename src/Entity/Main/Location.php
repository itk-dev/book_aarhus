<?php

namespace App\Entity\Main;

use ApiPlatform\Core\Annotation\ApiProperty;

class Location
{
    #[ApiProperty(identifier: true)]
    public string $name;
}
