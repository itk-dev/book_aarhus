<?php

namespace App\Entity\Trait;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait SourceIdTrait
{
    // ID in feed
    #[ORM\Column(type: Types::INTEGER, unique: true, nullable: false)]
    private int $sourceId;

    public function getSourceId(): int
    {
        return $this->sourceId;
    }

    public function setSourceId(int $sourceId): void
    {
        $this->sourceId = $sourceId;
    }
}
