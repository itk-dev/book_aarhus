<?php

namespace App\Entity\Resources;

use Doctrine\ORM\Mapping as ORM;

/**
 * Extbooking.holidayopenhours
 *
 * @ORM\Table(name="ExtBooking.holidayOpenHours")
 * @ORM\Entity
 */
class HolidayOpenHours
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
     * @var \DateTime
     *
     * @ORM\Column(name="holidayopen", type="time", nullable=false)
     */
    private $holidayopen;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="holidayclose", type="time", nullable=false)
     */
    private $holidayclose;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="UpdateTimeStamp", type="datetime", nullable=false)
     */
    private $updatetimestamp;


}
