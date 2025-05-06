<?php

namespace App\Entity\Main;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;


#[ORM\Entity]
class OpenHours
{
    #[ORM\Column(type: Types::INTEGER, nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[ORM\Column(type: Types::INTEGER, unique: true, nullable: false)]
    private string $sourceId;

    #[ORM\ManyToOne(targetEntity: Resource::class, inversedBy: 'openTimeHours')]
    private Resource $resource;

    #[Groups(['resource', 'minimum'])]
    #[ORM\Column(type: Types::INTEGER, nullable: false)]
    private int $weekday;

    #[Groups(['resource', 'minimum'])]
    #[ORM\Column(type: Types::TIME_MUTABLE, length: 0)]
    private readonly \DateTime $openTime;

    #[Groups(['resource', 'minimum'])]
    #[ORM\Column(type: Types::TIME_MUTABLE, length: 0)]
    private readonly \DateTime $closeTime;

    #[Groups(['resource'])]
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

    public function getWeekday(): int
    {
        return $this->weekday;
    }

    public function setWeekday(int $weekday): void
    {
        $this->weekday = $weekday;
    }

    public function getOpenTime(): \DateTime
    {
        return $this->openTime;
    }

    public function setOpenTime(\DateTime $openTime): void
    {
        $this->openTime = $openTime;
    }

    public function getCloseTime(): \DateTime
    {
        return $this->closeTime;
    }

    public function setCloseTime(\DateTime $closeTime): void
    {
        $this->closeTime = $closeTime;
    }

    public function getUpdateTimestamp(): \DateTime
    {
        return $this->updateTimestamp;
    }

    public function setUpdateTimestamp(\DateTime $updateTimestamp): void
    {
        $this->updateTimestamp = $updateTimestamp;
    }

    public function getResource(): Resource
    {
        return $this->resource;
    }

    public function setResource(Resource $resource): void
    {
        $this->resource = $resource;
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
