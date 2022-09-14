<?php

namespace App\Entity\Resources;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Extbooking.holidayopenhours.
 *
 * @ORM\Table(name="ExtBooking.holidayOpenHours")
 * @ORM\Entity
 */
class HolidayOpenHours
{
    /**
     * @ORM\Column(name="ID", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="AAKResource", inversedBy="holidayOpenHours")
     * @ORM\JoinColumn(name="resourceID", referencedColumnName="ID")
     */
    private AAKResource $resource;

    /**
     * @Groups({"resource"})
     *
     * @ORM\Column(name="holidayopen", type="time", nullable=false)
     */
    private \DateTime $holidayOpen;

    /**
     * @Groups({"resource"})
     *
     * @ORM\Column(name="holidayclose", type="time", nullable=false)
     */
    private \DateTime $holidayClose;

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
     * @return \DateTime
     */
    public function getHolidayOpen(): \DateTime
    {
        return $this->holidayOpen;
    }

    /**
     * @param \DateTime $holidayOpen
     */
    public function setHolidayOpen(\DateTime $holidayOpen): void
    {
        $this->holidayOpen = $holidayOpen;
    }

    /**
     * @return \DateTime
     */
    public function getHolidayClose(): \DateTime
    {
        return $this->holidayClose;
    }

    /**
     * @param \DateTime $holidayClose
     */
    public function setHolidayClose(\DateTime $holidayClose): void
    {
        $this->holidayClose = $holidayClose;
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
