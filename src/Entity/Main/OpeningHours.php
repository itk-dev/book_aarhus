<?php

namespace App\Entity\Main;

use App\Entity\Trait\IdTrait;
use App\Entity\Trait\ResourceIdTrait;
use App\Entity\Trait\SourceIdTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
class OpeningHours
{
    use IdTrait;
    use SourceIdTrait;
    use ResourceIdTrait;

    #[ORM\ManyToOne(targetEntity: Resource::class, inversedBy: 'openTimeHours')]
    #[ORM\JoinColumn(name: "resource_id", referencedColumnName: "source_id")]
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

    public function getResource(): Resource
    {
        return $this->resource;
    }

    public function setResource(Resource $resource): void
    {
        $this->resource = $resource;
    }
}
