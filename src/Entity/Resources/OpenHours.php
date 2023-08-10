<?php

namespace App\Entity\Resources;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Extbooking.openhours.
 *
 * @ORM\Table(name="ExtBooking.OpenHours")
 *
 * @ORM\Entity
 */
class OpenHours
{
    /**
     * @ORM\Column(name="ID", type="integer", nullable=false)
     *
     * @ORM\Id
     *
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="AAKResource", inversedBy="openHours")
     *
     * @ORM\JoinColumn(name="resourceID", referencedColumnName="ID")
     */
    private AAKResource $resource;

    /**
     * @Groups({"resource", "minimum"})
     *
     * @ORM\Column(name="weekday", type="integer", nullable=false)
     */
    private int $weekday;

    /**
     * @Groups({"resource", "minimum"})
     *
     * @see https://www.doctrine-project.org/projects/doctrine-orm/en/latest/reference/basic-mapping.html#quoting-reserved-words
     *
     * @ORM\Column(name="`open`", type="time", length=0)
     */
    private \DateTime $open;

    /**
     * @Groups({"resource", "minimum"})
     *
     * @see https://www.doctrine-project.org/projects/doctrine-orm/en/latest/reference/basic-mapping.html#quoting-reserved-words
     *
     * @ORM\Column(name="`close`", type="time", length=0)
     */
    private \DateTime $close;

    /**
     * @Groups({"resource"})
     *
     * @ORM\Column(name="UpdateTimeStamp", type="datetime", nullable=false)
     */
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
    public function getOpen(): \DateTime
    {
        return $this->open;
    }

    /**
     * @param \DateTime $open
     */
    public function setOpen(\DateTime $open): void
    {
        $this->open = $open;
    }

    /**
     * @return \DateTime
     */
    public function getClose(): \DateTime
    {
        return $this->close;
    }

    /**
     * @param \DateTime $close
     */
    public function setClose(\DateTime $close): void
    {
        $this->close = $close;
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
     * @return AAKResource
     */
    public function getResource(): AAKResource
    {
        return $this->resource;
    }

    /**
     * @param AAKResource $resource
     */
    public function setResource(AAKResource $resource): void
    {
        $this->resource = $resource;
    }
}
