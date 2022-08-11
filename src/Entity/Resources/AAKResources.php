<?php

namespace App\Entity\Resources;

use Doctrine\ORM\Mapping as ORM;

/**
 * Extbooking.aakresources
 *
 * @ORM\Table(name="ExtBooking.AAKResources")
 * @ORM\Entity
 */
class AAKResources
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
     * @var string
     *
     * @ORM\Column(name="ResourceMail", type="string", length=128, nullable=false)
     */
    private $resourcemail;

    /**
     * @var string
     *
     * @ORM\Column(name="ResourceName", type="string", length=128, nullable=false)
     */
    private $resourcename;

    /**
     * @var string|null
     *
     * @ORM\Column(name="ResourceImage", type="text", length=-1, nullable=true)
     */
    private $resourceimage;

    /**
     * @var string
     *
     * @ORM\Column(name="ResourceEmailText", type="text", length=-1, nullable=false)
     */
    private $resourceemailtext;

    /**
     * @var string
     *
     * @ORM\Column(name="Location", type="string", length=128, nullable=false)
     */
    private $location;

    /**
     * @var string
     *
     * @ORM\Column(name="LocationType", type="text", length=-1, nullable=false)
     */
    private $locationtype;

    /**
     * @var string|null
     *
     * @ORM\Column(name="GeoCordinates", type="string", length=128, nullable=true)
     */
    private $geocordinates;

    /**
     * @var int|null
     *
     * @ORM\Column(name="Capacity", type="bigint", nullable=true)
     */
    private $capacity;

    /**
     * @var string
     *
     * @ORM\Column(name="Type", type="text", length=-1, nullable=false)
     */
    private $type;

    /**
     * @var string|null
     *
     * @ORM\Column(name="ResourceDescription", type="text", length=-1, nullable=true)
     */
    private $resourcedescription;

    /**
     * @var bool
     *
     * @ORM\Column(name="WheelChairAccessible", type="boolean", nullable=false)
     */
    private $wheelchairaccessible;

    /**
     * @var bool
     *
     * @ORM\Column(name="VideoConferenceEquipment", type="boolean", nullable=false)
     */
    private $videoconferenceequipment;

    /**
     * @var bool
     *
     * @ORM\Column(name="MonitorEquipment", type="boolean", nullable=false)
     */
    private $monitorequipment;

    /**
     * @var string
     *
     * @ORM\Column(name="BookingRights", type="text", length=-1, nullable=false)
     */
    private $bookingrights;

    /**
     * @var bool
     *
     * @ORM\Column(name="AcceptanceFlow", type="boolean", nullable=false)
     */
    private $acceptanceflow;

    /**
     * @var string|null
     *
     * @ORM\Column(name="Opening_Hours", type="text", length=-1, nullable=true)
     */
    private $openingHours;

    /**
     * @var string|null
     *
     * @ORM\Column(name="Holiday_Opening_Hours", type="text", length=-1, nullable=true)
     */
    private $holidayOpeningHours;

    /**
     * @var string|null
     *
     * @ORM\Column(name="Whitelist", type="text", length=-1, nullable=true)
     */
    private $whitelist;

    /**
     * @var bool
     *
     * @ORM\Column(name="Catering", type="boolean", nullable=false)
     */
    private $catering;

    /**
     * @var string|null
     *
     * @ORM\Column(name="FormID", type="text", length=-1, nullable=true)
     */
    private $formid;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="UpdateTimeStamp", type="datetime", nullable=false)
     */
    private $updatetimestamp;


}
