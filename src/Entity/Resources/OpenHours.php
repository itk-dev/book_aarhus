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
    private int $id;

    /**
     * @var int
     *
     * @ORM\Column(name="resourceID", type="integer", nullable=false)
     */
    private int $resourceId;

    /**
     * @var int
     *
     * @ORM\Column(name="weekday", type="integer", nullable=false)
     */
    private int $weekday;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="open", type="time", nullable=false)
     */
    private \DateTime $open;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="close", type="time", nullable=false)
     */
    private \DateTime $close;

    /**
     * @var \DateTime
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
    public function getResourceId(): int
    {
        return $this->resourceId;
    }

    /**
     * @param int $resourceId
     */
    public function setResourceId(int $resourceId): void
    {
        $this->resourceId = $resourceId;
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
}
