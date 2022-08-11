<?php

namespace App\Entity\Resources;

use Doctrine\ORM\Mapping as ORM;

/**
 * Extbooking.cvrwhitelist
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


}
