<?php

namespace App\Entity\Resources;

use Doctrine\ORM\Mapping as ORM;

/**
 * Extbooking.aakresources.
 *
 * @ORM\Table(name="ExtBooking.AAKResources")
 * @ORM\Entity
 */
class AAKResource
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

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getResourcemail(): string
    {
        return $this->resourcemail;
    }

    /**
     * @param string $resourcemail
     */
    public function setResourcemail(string $resourcemail): void
    {
        $this->resourcemail = $resourcemail;
    }

    /**
     * @return string
     */
    public function getResourcename(): string
    {
        return $this->resourcename;
    }

    /**
     * @param string $resourcename
     */
    public function setResourcename(string $resourcename): void
    {
        $this->resourcename = $resourcename;
    }

    /**
     * @return string|null
     */
    public function getResourceimage(): ?string
    {
        return $this->resourceimage;
    }

    /**
     * @param string|null $resourceimage
     */
    public function setResourceimage(?string $resourceimage): void
    {
        $this->resourceimage = $resourceimage;
    }

    /**
     * @return string
     */
    public function getResourceemailtext(): string
    {
        return $this->resourceemailtext;
    }

    /**
     * @param string $resourceemailtext
     */
    public function setResourceemailtext(string $resourceemailtext): void
    {
        $this->resourceemailtext = $resourceemailtext;
    }

    /**
     * @return string
     */
    public function getLocation(): string
    {
        return $this->location;
    }

    /**
     * @param string $location
     */
    public function setLocation(string $location): void
    {
        $this->location = $location;
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
     * @return string|null
     */
    public function getGeocordinates(): ?string
    {
        return $this->geocordinates;
    }

    /**
     * @param string|null $geocordinates
     */
    public function setGeocordinates(?string $geocordinates): void
    {
        $this->geocordinates = $geocordinates;
    }

    /**
     * @return int|null
     */
    public function getCapacity(): ?int
    {
        return $this->capacity;
    }

    /**
     * @param int|null $capacity
     */
    public function setCapacity(?int $capacity): void
    {
        $this->capacity = $capacity;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return string|null
     */
    public function getResourcedescription(): ?string
    {
        return $this->resourcedescription;
    }

    /**
     * @param string|null $resourcedescription
     */
    public function setResourcedescription(?string $resourcedescription): void
    {
        $this->resourcedescription = $resourcedescription;
    }

    /**
     * @return bool
     */
    public function isWheelchairaccessible(): bool
    {
        return $this->wheelchairaccessible;
    }

    /**
     * @param bool $wheelchairaccessible
     */
    public function setWheelchairaccessible(bool $wheelchairaccessible): void
    {
        $this->wheelchairaccessible = $wheelchairaccessible;
    }

    /**
     * @return bool
     */
    public function isVideoconferenceequipment(): bool
    {
        return $this->videoconferenceequipment;
    }

    /**
     * @param bool $videoconferenceequipment
     */
    public function setVideoconferenceequipment(bool $videoconferenceequipment): void
    {
        $this->videoconferenceequipment = $videoconferenceequipment;
    }

    /**
     * @return bool
     */
    public function isMonitorequipment(): bool
    {
        return $this->monitorequipment;
    }

    /**
     * @param bool $monitorequipment
     */
    public function setMonitorequipment(bool $monitorequipment): void
    {
        $this->monitorequipment = $monitorequipment;
    }

    /**
     * @return string
     */
    public function getBookingrights(): string
    {
        return $this->bookingrights;
    }

    /**
     * @param string $bookingrights
     */
    public function setBookingrights(string $bookingrights): void
    {
        $this->bookingrights = $bookingrights;
    }

    /**
     * @return bool
     */
    public function isAcceptanceflow(): bool
    {
        return $this->acceptanceflow;
    }

    /**
     * @param bool $acceptanceflow
     */
    public function setAcceptanceflow(bool $acceptanceflow): void
    {
        $this->acceptanceflow = $acceptanceflow;
    }

    /**
     * @return string|null
     */
    public function getOpeningHours(): ?string
    {
        return $this->openingHours;
    }

    /**
     * @param string|null $openingHours
     */
    public function setOpeningHours(?string $openingHours): void
    {
        $this->openingHours = $openingHours;
    }

    /**
     * @return string|null
     */
    public function getHolidayOpeningHours(): ?string
    {
        return $this->holidayOpeningHours;
    }

    /**
     * @param string|null $holidayOpeningHours
     */
    public function setHolidayOpeningHours(?string $holidayOpeningHours): void
    {
        $this->holidayOpeningHours = $holidayOpeningHours;
    }

    /**
     * @return string|null
     */
    public function getWhitelist(): ?string
    {
        return $this->whitelist;
    }

    /**
     * @param string|null $whitelist
     */
    public function setWhitelist(?string $whitelist): void
    {
        $this->whitelist = $whitelist;
    }

    /**
     * @return bool
     */
    public function isCatering(): bool
    {
        return $this->catering;
    }

    /**
     * @param bool $catering
     */
    public function setCatering(bool $catering): void
    {
        $this->catering = $catering;
    }

    /**
     * @return string|null
     */
    public function getFormid(): ?string
    {
        return $this->formid;
    }

    /**
     * @param string|null $formid
     */
    public function setFormid(?string $formid): void
    {
        $this->formid = $formid;
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
