<?php

namespace App\Entity\Resources;

use Doctrine\ORM\Mapping as ORM;

/**
 * Extbooking.cvrwhitelist.
 *
 * @ORM\Table(name="ExtBooking.cvrWhiteList")
 * @ORM\Entity
 */
class CvrWhitelist
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
     * @ORM\Column(name="cvr", type="integer", nullable=false)
     */
    private $cvr;

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
