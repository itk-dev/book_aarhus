<?php

namespace App\Entity\Main;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
class HolidayOpenHours
{
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::INTEGER, nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Resource::class, inversedBy: 'holidayOpenHours')]
    private Resource $resource;

    #[Groups(['resource', 'minimum'])]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::TIME_MUTABLE, nullable: false)]
    private \DateTime $holidayOpen;

    #[Groups(['resource', 'minimum'])]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::TIME_MUTABLE, nullable: false)]
    private \DateTime $holidayClose;

    #[Groups(['resource'])]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE, nullable: false)]
    private \DateTime $updateTimestamp;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

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

    public function getUpdateTimestamp(): \DateTime
    {
        return $this->updateTimestamp;
    }

    public function setUpdateTimestamp(\DateTime $updateTimestamp): void
    {
        $this->updateTimestamp = $updateTimestamp;
    }
}
