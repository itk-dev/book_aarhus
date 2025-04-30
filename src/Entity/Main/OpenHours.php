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

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getWeekday(): int
    {
        return $this->weekday;
    }

    /**
     * @param int $weekday
     */
    public function setWeekday(int $weekday): void
    {
        $this->weekday = $weekday;
    }

    /**
     * @return \DateTime
     */
    public function getOpenTime(): \DateTime
    {
        return $this->openTime;
    }

    /**
     * @param \DateTime $openTime
     */
    public function setOpenTime(\DateTime $openTime): void
    {
        $this->openTime = $openTime;
    }

    /**
     * @return \DateTime
     */
    public function getCloseTime(): \DateTime
    {
        return $this->closeTime;
    }

    /**
     * @param \DateTime $closeTime
     */
    public function setCloseTime(\DateTime $closeTime): void
    {
        $this->closeTime = $closeTime;
    }

    /**
     * @return \DateTime
     */
    public function getUpdateTimestamp(): \DateTime
    {
        return $this->updateTimestamp;
    }

    /**
     * @param \DateTime $updateTimestamp
     */
    public function setUpdateTimestamp(\DateTime $updateTimestamp): void
    {
        $this->updateTimestamp = $updateTimestamp;
    }

    /**
     * @return Resource
     */
    public function getResource(): Resource
    {
        return $this->resource;
    }

    /**
     * @param Resource $resource
     */
    public function setResource(Resource $resource): void
    {
        $this->resource = $resource;
    }
}
