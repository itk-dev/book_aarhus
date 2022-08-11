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
    private int $id;

    /**
     * @var int
     *
     * @ORM\Column(name="resourceID", type="integer", nullable=false)
     */
    private int $resourceid;

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
}
