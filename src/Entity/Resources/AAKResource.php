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
    private int $id;

    /**
     * @var string
     *
     * @ORM\Column(name="ResourceMail", type="string", length=128, nullable=false)
     */
    private string $resourceEmail;

    /**
     * @var string
     *
     * @ORM\Column(name="ResourceName", type="string", length=128, nullable=false)
     */
    private string $resourceName;

    /**
     * @var string|null
     *
     * @ORM\Column(name="ResourceImage", type="text", length=-1, nullable=true)
     */
    private ?string $resourceImage;

    /**
     * @var string|null
     *
     * @ORM\Column(name="ResourceEmailText", type="text", length=-1, nullable=true)
     */
    private ?string $resourceEmailText;

    /**
     * @var string
     *
     * @ORM\Column(name="Location", type="string", length=128, nullable=false)
     */
    private string $location;

    /**
     * @var string|null
     *
     * @ORM\Column(name="GeoCordinates", type="string", length=128, nullable=true)
     */
    private ?string $geoCoordinates;

    /**
     * @var int|null
     *
     * @ORM\Column(name="Capacity", type="bigint", nullable=true)
     */
    private ?int $capacity;

    /**
     * @var string|null
     *
     * @ORM\Column(name="ResourceDescription", type="text", length=-1, nullable=true)
     */
    private ?string $resourceDescription;

    /**
     * @var bool
     *
     * @ORM\Column(name="WheelChairAccessible", type="boolean", nullable=false)
     */
    private bool $wheelchairAccessible;

    /**
     * @var bool
     *
     * @ORM\Column(name="VideoConferenceEquipment", type="boolean", nullable=false)
     */
    private bool $videoConferenceEquipment;

    /**
     * @var bool
     *
     * @ORM\Column(name="MonitorEquipment", type="boolean", nullable=false)
     */
    private bool $monitorEquipment;

    /**
     * @var bool
     *
     * @ORM\Column(name="AcceptanceFlow", type="boolean", nullable=false)
     */
    private bool $acceptanceFlow;

    /**
     * @var bool
     *
     * @ORM\Column(name="Catering", type="boolean", nullable=false)
     */
    private bool $catering;

    /**
     * @var string|null
     *
     * @ORM\Column(name="FormID", type="text", length=-1, nullable=true)
     */
    private ?string $formId;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="HasHolidayOpen", type="boolean", nullable=true)
     */
    private ?bool $hasHolidayOpen;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="HasOpen", type="boolean", nullable=true)
     */
    private ?bool $hasOpen;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="HasWhiteList", type="boolean", nullable=true)
     */
    private ?bool $hasWhitelist;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="PermissionEmployee", type="boolean", nullable=true)
     */
    private ?bool $permissionEmployee;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="PermissionCitizen", type="boolean", nullable=true)
     */
    private ?bool $permissionCitizen;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="PermissionBusinessPartner", type="boolean", nullable=true)
     */
    private ?bool $permissionBusinessPartner;

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
     * @return string
     */
    public function getResourceEmail(): string
    {
        return $this->resourceEmail;
    }

    /**
     * @param string $resourceEmail
     */
    public function setResourceEmail(string $resourceEmail): void
    {
        $this->resourceEmail = $resourceEmail;
    }

    /**
     * @return string
     */
    public function getResourceName(): string
    {
        return $this->resourceName;
    }

    /**
     * @param string $resourceName
     */
    public function setResourceName(string $resourceName): void
    {
        $this->resourceName = $resourceName;
    }

    /**
     * @return string|null
     */
    public function getResourceImage(): ?string
    {
        return $this->resourceImage;
    }

    /**
     * @param string|null $resourceImage
     */
    public function setResourceImage(?string $resourceImage): void
    {
        $this->resourceImage = $resourceImage;
    }

    /**
     * @return string|null
     */
    public function getResourceEmailText(): ?string
    {
        return $this->resourceEmailText;
    }

    /**
     * @param string|null $resourceEmailText
     */
    public function setResourceEmailText(?string $resourceEmailText): void
    {
        $this->resourceEmailText = $resourceEmailText;
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
     * @return string|null
     */
    public function getGeoCoordinates(): ?string
    {
        return $this->geoCoordinates;
    }

    /**
     * @param string|null $geoCoordinates
     */
    public function setGeoCoordinates(?string $geoCoordinates): void
    {
        $this->geoCoordinates = $geoCoordinates;
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
     * @return string|null
     */
    public function getResourceDescription(): ?string
    {
        return $this->resourceDescription;
    }

    /**
     * @param string|null $resourceDescription
     */
    public function setResourceDescription(?string $resourceDescription): void
    {
        $this->resourceDescription = $resourceDescription;
    }

    /**
     * @return bool
     */
    public function isWheelchairAccessible(): bool
    {
        return $this->wheelchairAccessible;
    }

    /**
     * @param bool $wheelchairAccessible
     */
    public function setWheelchairAccessible(bool $wheelchairAccessible): void
    {
        $this->wheelchairAccessible = $wheelchairAccessible;
    }

    /**
     * @return bool
     */
    public function isVideoConferenceEquipment(): bool
    {
        return $this->videoConferenceEquipment;
    }

    /**
     * @param bool $videoConferenceEquipment
     */
    public function setVideoConferenceEquipment(bool $videoConferenceEquipment): void
    {
        $this->videoConferenceEquipment = $videoConferenceEquipment;
    }

    /**
     * @return bool
     */
    public function isMonitorEquipment(): bool
    {
        return $this->monitorEquipment;
    }

    /**
     * @param bool $monitorEquipment
     */
    public function setMonitorEquipment(bool $monitorEquipment): void
    {
        $this->monitorEquipment = $monitorEquipment;
    }

    /**
     * @return bool
     */
    public function isAcceptanceFlow(): bool
    {
        return $this->acceptanceFlow;
    }

    /**
     * @param bool $acceptanceFlow
     */
    public function setAcceptanceFlow(bool $acceptanceFlow): void
    {
        $this->acceptanceFlow = $acceptanceFlow;
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
    public function getFormId(): ?string
    {
        return $this->formId;
    }

    /**
     * @param string|null $formId
     */
    public function setFormId(?string $formId): void
    {
        $this->formId = $formId;
    }

    /**
     * @return bool|null
     */
    public function getHasHolidayOpen(): ?bool
    {
        return $this->hasHolidayOpen;
    }

    /**
     * @param bool|null $hasHolidayOpen
     */
    public function setHasHolidayOpen(?bool $hasHolidayOpen): void
    {
        $this->hasHolidayOpen = $hasHolidayOpen;
    }

    /**
     * @return bool|null
     */
    public function getHasOpen(): ?bool
    {
        return $this->hasOpen;
    }

    /**
     * @param bool|null $hasOpen
     */
    public function setHasOpen(?bool $hasOpen): void
    {
        $this->hasOpen = $hasOpen;
    }

    /**
     * @return bool|null
     */
    public function getHasWhitelist(): ?bool
    {
        return $this->hasWhitelist;
    }

    /**
     * @param bool|null $hasWhitelist
     */
    public function setHasWhitelist(?bool $hasWhitelist): void
    {
        $this->hasWhitelist = $hasWhitelist;
    }

    /**
     * @return bool|null
     */
    public function getPermissionEmployee(): ?bool
    {
        return $this->permissionEmployee;
    }

    /**
     * @param bool|null $permissionEmployee
     */
    public function setPermissionEmployee(?bool $permissionEmployee): void
    {
        $this->permissionEmployee = $permissionEmployee;
    }

    /**
     * @return bool|null
     */
    public function getPermissionCitizen(): ?bool
    {
        return $this->permissionCitizen;
    }

    /**
     * @param bool|null $permissionCitizen
     */
    public function setPermissionCitizen(?bool $permissionCitizen): void
    {
        $this->permissionCitizen = $permissionCitizen;
    }

    /**
     * @return bool|null
     */
    public function getPermissionBusinessPartner(): ?bool
    {
        return $this->permissionBusinessPartner;
    }

    /**
     * @param bool|null $permissionBusinessPartner
     */
    public function setPermissionBusinessPartner(?bool $permissionBusinessPartner): void
    {
        $this->permissionBusinessPartner = $permissionBusinessPartner;
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
