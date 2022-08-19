<?php

namespace App\Entity\Main;

use ApiPlatform\Core\Annotation\ApiProperty;

class Location
{
    #[ApiProperty(identifier: true)]
    private string $name;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
