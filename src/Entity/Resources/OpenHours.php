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
}
