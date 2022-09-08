<?php

namespace App\Entity\Resources;

use Doctrine\ORM\Mapping as ORM;

/**
 * Extbooking.locationtype.
 *
 * @ORM\Table(name="ExtBooking.locationType")
 * @ORM\Entity
 */
class LocationType
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
     * @var string
     *
     * @ORM\Column(name="locationType", type="string", length=512, nullable=false)
     */
    private $locationtype;

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
     * @return string
     */
    public function getLocationtype(): string
    {
        return $this->locationtype;
    }

    /**
     * @param string $locationtype
     */
    public function setLocationtype(string $locationtype): void
    {
        $this->locationtype = $locationtype;
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
