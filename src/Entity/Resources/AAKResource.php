<?php

namespace App\Entity\Resources;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Controller\GetAllResourcesController;
use App\Controller\GetResourceByEmailController;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Extbooking.aakresources.
 *
 * @ORM\Table(name="ExtBooking.AAKResources")
 * @ORM\Entity
 */
#[ApiResource(
    shortName: 'Resource',
    description: 'Resource test',
    operations: [
        new Get(
            uriTemplate: '/resources/{id}',
            normalizationContext: [
                'groups' => ['resource'],
            ],
        ),
        new Get(
            uriTemplate: '/resource-by-email/{resourceMail}',
            controller: GetResourceByEmailController::class,
            openapiContext: [
                'description' => 'Get a resource by email',
                'summary' => 'Get a resource by email',
                'operationId' => 'get-v1-resource-by-email',

                'parameters' => [
                    [
                        'schema' => [
                            'type' => 'string',
                            'format' => 'string',
                            'example' => 'test@example.com',
                        ],
                        'in' => 'path',
                        'required' => true,
                        'description' => 'Resource mail',
                        'name' => 'resourceMail',
                    ],
                ],

                'response' => [
                    '200' => [
                        'description' => 'OK',
                    ],
                ],
            ],
            normalizationContext: [
                'groups' => ['resource'],
            ],
            name: 'get_by_email',
        ),

        new GetCollection(
            uriTemplate: '/resources',
            normalizationContext: ['groups' => ['resource']],
            filters: ['resource.search_filter', 'resource.boolean_filter', 'resource.range_filter'],
        ),

        new GetCollection(
            uriTemplate: '/resources-all',
            controller: GetAllResourcesController::class,
            openapiContext: [
                'description' => 'Get all resources in a minified view.',
                'summary' => 'Get all resources.',
                'operationId' => 'get-v1-all-resources',

                'parameters' => [],
                'response' => [
                    '200' => [
                        'description' => 'OK',
                    ],
                ],
            ],
            normalizationContext: ['groups' => ['resource']],
            name: 'get_all'
        ),
    ],
    normalizationContext: [
        'groups' => ['resource'],
    ]
)]
class AAKResource
{
    /**
     * @Groups({"resource", "minimum"})
     * @ORM\Column(name="ID", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private int $id;

    /**
     * @Groups({"resource", "minimum"})
     * @ORM\OneToMany(targetEntity="OpenHours", mappedBy="resource")
     */
    private Collection $openHours;

    /**
     * @Groups({"resource", "minimum"})
     * @ORM\OneToMany(targetEntity="HolidayOpenHours", mappedBy="resource")
     */
    private Collection $holidayOpenHours;

    /**
     * @Groups({"resource", "minimum"})
     * @ORM\Column(name="ResourceMail", type="string", length=128, nullable=false)
     */
    private string $resourceMail;

    /**
     * @Groups({"resource", "minimum"})
     * @ORM\Column(name="ResourceName", type="string", length=128, nullable=false)
     */
    private string $resourceName;

    /**
     * @Groups({"resource", "minimum"})
     * @ORM\Column(name="ResourceImage", type="text", length=-1, nullable=true)
     */
    private ?string $resourceImage;

    /**
     * @Groups({"resource"})
     * @ORM\Column(name="ResourceEmailText", type="text", length=-1, nullable=true)
     */
    private ?string $resourceEmailText;

    /**
     * @Groups({"resource", "minimum"})
     * @ORM\Column(name="Location", type="string", length=128, nullable=false)
     */
    private string $location;

    /**
     * @Groups({"resource", "minimum"})
     * @ORM\Column(name="GeoCordinates", type="string", length=128, nullable=true)
     */
    private ?string $geoCoordinates;

    /**
     * @Groups({"resource", "minimum"})
     * @ORM\Column(name="Capacity", type="bigint", nullable=true)
     */
    private ?int $capacity;

    /**
     * @Groups({"resource", "minimum"})
     * @ORM\Column(name="ResourceDescription", type="text", length=-1, nullable=true)
     */
    private ?string $resourceDescription;

    /**
     * @Groups({"resource", "minimum"})
     * @ORM\Column(name="WheelChairAccessible", type="boolean", nullable=false)
     */
    private bool $wheelchairAccessible;

    /**
     * @Groups({"resource", "minimum"})
     * @ORM\Column(name="VideoConferenceEquipment", type="boolean", nullable=false)
     */
    private bool $videoConferenceEquipment;

    /**
     * @Groups({"resource", "minimum"})
     * @ORM\Column(name="MonitorEquipment", type="boolean", nullable=false)
     */
    private bool $monitorEquipment;

    /**
     * @Groups({"resource", "minimum"})
     * @ORM\Column(name="AcceptanceFlow", type="boolean", nullable=false)
     */
    private bool $acceptanceFlow;

    /**
     * @Groups({"resource", "minimum"})
     * @ORM\Column(name="Catering", type="boolean", nullable=false)
     */
    private bool $catering;

    /**
     * @Groups({"resource", "minimum"})
     * @ORM\Column(name="FormID", type="text", length=-1, nullable=true)
     */
    private ?string $formId;

    /**
     * @Groups({"resource", "minimum"})
     * @ORM\Column(name="HasHolidayOpen", type="boolean", nullable=true)
     */
    private ?bool $hasHolidayOpen;

    /**
     * @Groups({"resource", "minimum"})
     * @ORM\Column(name="HasOpen", type="boolean", nullable=true)
     */
    private ?bool $hasOpen;

    /**
     * @Groups({"resource", "minimum"})
     * @ORM\Column(name="HasWhiteList", type="boolean", nullable=true)
     */
    private ?bool $hasWhitelist;

    /**
     * @Groups({"resource"})
     * @ORM\Column(name="PermissionEmployee", type="boolean", nullable=true)
     */
    private ?bool $permissionEmployee;

    /**
     * @Groups({"resource"})
     * @ORM\Column(name="PermissionCitizen", type="boolean", nullable=true)
     */
    private ?bool $permissionCitizen;

    /**
     * @Groups({"resource"})
     * @ORM\Column(name="PermissionBusinessPartner", type="boolean", nullable=true)
     */
    private ?bool $permissionBusinessPartner;

    /**
     * @Groups({"resource"})
     * @ORM\Column(name="UpdateTimeStamp", type="datetime", nullable=false)
     */
    private \DateTime $updateTimestamp;

    /**
     * @Groups({"resource", "minimum"})
     * @ORM\Column(name="DisplayName", type="string", length=128, nullable=true)
     */
    private ?string $displayName;

    /**
     * @Groups({"resource", "minimum"})
     * @ORM\Column(name="City", type="string", length=128, nullable=true)
     */
    private ?string $city;

    /**
     * @Groups({"resource", "minimum"})
     * @ORM\Column(name="StreetName", type="string", length=128, nullable=true)
     */
    private ?string $streetName;

    /**
     * @Groups({"resource", "minimum"})
     * @ORM\Column(name="PostalCode", type="integer", nullable=true)
     */
    private ?int $postalCode;

    /**
     * @Groups({"resource", "minimum"})
     * @ORM\Column(name="RessourceCategory", type="string", length=128, nullable=true)
     */
    private ?string $resourceCategory;

    /**
     * @Groups({"resource", "minimum"})
     * @ORM\Column(name="ResourceDisplayName", type="string", length=128, nullable=true)
     */
    private ?string $resourceDisplayName;

    /**
     * @Groups({"resource", "minimum"})
     * @ORM\Column(name="LocationDisplayName", type="string", length=128, nullable=true)
     */
    private ?string $locationDisplayName;

    /**
     * @Groups({"resource", "minimum"})
     * @ORM\Column(name="AcceptConflict", type="boolean", nullable=true)
     */
    private ?bool $acceptConflict;

    public function __construct()
    {
        $this->openHours = new ArrayCollection();
        $this->holidayOpenHours = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Collection
     */
    public function getOpenHours(): Collection
    {
        return $this->openHours;
    }

    /**
     * @param Collection $openHours
     */
    public function setOpenHours(Collection $openHours): void
    {
        $this->openHours = $openHours;
    }

    /**
     * @return Collection
     */
    public function getHolidayOpenHours(): Collection
    {
        return $this->holidayOpenHours;
    }

    /**
     * @param Collection $holidayOpenHours
     */
    public function setHolidayOpenHours(Collection $holidayOpenHours): void
    {
        $this->holidayOpenHours = $holidayOpenHours;
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
    public function getResourceMail(): string
    {
        return $this->resourceMail;
    }

    /**
     * @param string $resourceMail
     */
    public function setResourceMail(string $resourceMail): void
    {
        $this->resourceMail = $resourceMail;
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

    /**
     * @return string|null
     */
    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    /**
     * @param string|null $displayName
     */
    public function setDisplayName(?string $displayName): void
    {
        $this->displayName = $displayName;
    }

    /**
     * @return string|null
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * @param string|null $city
     */
    public function setCity(?string $city): void
    {
        $this->city = $city;
    }

    /**
     * @return string|null
     */
    public function getStreetName(): ?string
    {
        return $this->streetName;
    }

    /**
     * @param string|null $streetName
     */
    public function setStreetName(?string $streetName): void
    {
        $this->streetName = $streetName;
    }

    /**
     * @return int|null
     */
    public function getPostalCode(): ?int
    {
        return $this->postalCode;
    }

    /**
     * @param int|null $postalCode
     */
    public function setPostalCode(?int $postalCode): void
    {
        $this->postalCode = $postalCode;
    }

    /**
     * @return string|null
     */
    public function getResourceCategory(): ?string
    {
        return $this->resourceCategory;
    }

    /**
     * @param string|null $resourceCategory
     */
    public function setResourceCategory(?string $resourceCategory): void
    {
        $this->resourceCategory = $resourceCategory;
    }

    /**
     * @return string|null
     */
    public function getLocationDisplayName(): ?string
    {
        return $this->locationDisplayName;
    }

    /**
     * @param string|null $locationDisplayName
     */
    public function setLocationDisplayName(?string $locationDisplayName): void
    {
        $this->locationDisplayName = $locationDisplayName;
    }

    /**
     * @return string|null
     */
    public function getResourceDisplayName(): ?string
    {
        return $this->resourceDisplayName;
    }

    /**
     * @param string|null $resourceDisplayName
     */
    public function setResourceDisplayName(?string $resourceDisplayName): void
    {
        $this->resourceDisplayName = $resourceDisplayName;
    }

    /**
     * @return bool|null
     */
    public function getAcceptConflict(): ?bool
    {
        return $this->acceptConflict;
    }

    /**
     * @param bool|null $acceptConflict
     */
    public function setAcceptConflict(?bool $acceptConflict): void
    {
        $this->acceptConflict = $acceptConflict;
    }
}
