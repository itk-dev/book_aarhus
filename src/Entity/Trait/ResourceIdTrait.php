<?php

namespace App\Entity\Trait;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait ResourceIdTrait
{
    // ID of the resource in feed
    #[ORM\Column(type: Types::INTEGER, nullable: false)]
    private int $resourceId;

    public function getResourceId(): int
    {
        return $this->resourceId;
    }

    public function setResourceId(int $resourceId): void
    {
        $this->resourceId = $resourceId;
    }
}
