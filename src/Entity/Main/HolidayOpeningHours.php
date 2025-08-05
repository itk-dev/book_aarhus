<?php

namespace App\Entity\Main;

use App\Entity\Trait\IdTrait;
use App\Entity\Trait\ResourceIdTrait;
use App\Entity\Trait\SourceIdTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
class HolidayOpeningHours
{
    use IdTrait;
    use SourceIdTrait;

    #[ORM\ManyToOne(targetEntity: Resource::class, inversedBy: 'holidayOpenHours')]
    private Resource $resource;

    #[Groups(['resource', 'minimum'])]
    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: false)]
    private \DateTime $holidayOpen;

    #[Groups(['resource', 'minimum'])]
    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: false)]
    private \DateTime $holidayClose;

    public function getResource(): Resource
    {
        return $this->resource;
    }

    public function setResource(Resource $resource): void
    {
        $this->resource = $resource;
    }

    public function getHolidayOpen(): \DateTime
    {
        return $this->holidayOpen;
    }

    public function setHolidayOpen(\DateTime $holidayOpen): void
    {
        $this->holidayOpen = $holidayOpen;
    }

    public function getHolidayClose(): \DateTime
    {
        return $this->holidayClose;
    }

    public function setHolidayClose(\DateTime $holidayClose): void
    {
        $this->holidayClose = $holidayClose;
    }
}
