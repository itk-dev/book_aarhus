<?php

namespace App\Entity\Resources;

use Doctrine\ORM\Mapping as ORM;

/**
 * Extbooking.cvrwhitelist.
 *
 * @ORM\Table(name="ExtBooking.cvrWhiteList")
 *
 * @ORM\Entity
 */
class CvrWhitelist
{
    /**
     * @var int
     *
     * @ORM\Column(name="ID", type="integer", nullable=false)
     *
     * @ORM\Id
     *
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
     * @ORM\Column(name="cvr", type="integer", nullable=false)
     */
    private int $cvr;

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
    public function getCvr(): int
    {
        return $this->cvr;
    }

    /**
     * @param int $cvr
     */
    public function setCvr(int $cvr): void
    {
        $this->cvr = $cvr;
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
