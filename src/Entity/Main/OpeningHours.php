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

    #[ORM\ManyToOne(targetEntity: Resource::class, inversedBy: 'openHours')]
    private Resource $resource;

    #[Groups(['resource', 'minimum'])]
    #[ORM\Column(type: Types::INTEGER, nullable: false)]
    private int $weekday;

    #[Groups(['resource', 'minimum'])]
    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private \DateTime $open;

    #[Groups(['resource', 'minimum'])]
    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private \DateTime $close;

    public function getWeekday(): int
    {
        return $this->weekday;
    }

    public function setWeekday(int $weekday): void
    {
        $this->weekday = $weekday;
    }

    public function getOpen(): \DateTime
    {
        return $this->open;
    }

    public function setOpen(\DateTime $open): void
    {
        $this->open = $open;
    }

    public function getClose(): \DateTime
    {
        return $this->close;
    }

    public function setClose(\DateTime $close): void
    {
        $this->close = $close;
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
