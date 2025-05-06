<?php

namespace App\Entity\Main;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
class CvrWhitelist
{
    #[ORM\Column(type: Types::INTEGER, nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[ORM\Column(type: Types::INTEGER, unique: true, nullable: false)]
    private string $sourceId;

    #[ORM\Column(type: Types::INTEGER, nullable: false)]
    private int $resourceId;

    #[ORM\Column(type: Types::INTEGER, nullable: false)]
    private int $cvr;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    private \DateTime $updateTimestamp;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getResourceId(): int
    {
        return $this->resourceId;
    }

    public function setResourceId(int $resourceId): void
    {
        $this->resourceId = $resourceId;
    }

    public function getCvr(): int
    {
        return $this->cvr;
    }

    public function setCvr(int $cvr): void
    {
        $this->cvr = $cvr;
    }

    public function getUpdateTimestamp(): \DateTime
    {
        return $this->updateTimestamp;
    }

    public function setUpdateTimestamp(\DateTime $updateTimestamp): void
    {
        $this->updateTimestamp = $updateTimestamp;
    }

    public function getSourceId(): string
    {
        return $this->sourceId;
    }

    public function setSourceId(string $sourceId): void
    {
        $this->sourceId = $sourceId;
    }
}
