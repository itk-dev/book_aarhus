<?php

namespace App\Entity\Resources;

use Doctrine\ORM\Mapping as ORM;

/**
 * Extbooking.openhours.
 *
 * @ORM\Table(name="ExtBooking.OpenHours")
 * @ORM\Entity
 */
class OpenHours
{
    /**
     * @var int
     *
     * @ORM\Column(name="ID", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="resourceID", type="integer", nullable=false)
     */
    private $resourceid;

    /**
     * @var int
     *
     * @ORM\Column(name="weekday", type="integer", nullable=false)
     */
    private $weekday;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="open", type="time", nullable=false)
     */
    private $open;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="close", type="time", nullable=false)
     */
    private $close;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="UpdateTimeStamp", type="datetime", nullable=false)
     */
    private $updatetimestamp;

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
    public function getResourceid(): int
    {
        return $this->resourceid;
    }

    /**
     * @param int $resourceid
     */
    public function setResourceid(int $resourceid): void
    {
        $this->resourceid = $resourceid;
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
    public function getUpdatetimestamp(): \DateTime
    {
        return $this->updatetimestamp;
    }

    /**
     * @param \DateTime $updatetimestamp
     */
    public function setUpdatetimestamp(\DateTime $updatetimestamp): void
    {
        $this->updatetimestamp = $updatetimestamp;
    }
}
