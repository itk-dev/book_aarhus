<?php

namespace App\Entity\Main;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Controller\GetAllResourcesController;
use App\Controller\GetResourceByEmailController;
use App\Entity\Trait\IdTrait;
use App\Entity\Trait\SourceIdTrait;
use App\Repository\ResourceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ResourceRepository::class)]
#[ApiResource(
    shortName: 'Resource',
    description: 'Resource',
    operations: [
        new Get(
            uriTemplate: '/resources/{id}',
            openapiContext: ['operationId' => 'getResourceItem'],
            normalizationContext: [
                'groups' => ['resource'],
            ]
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
            read: false,
            name: 'get_by_email',
        ),

        new GetCollection(
            uriTemplate: '/resources',
            openapiContext: ['operationId' => 'getResourceCollection'],
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
class Resource
{
    use IdTrait;
    use SourceIdTrait;

    /**
     * @var Collection<int, OpeningHours>
     */
    #[Groups(['resource', 'minimum'])]
    #[ORM\OneToMany(mappedBy: 'resource', targetEntity: OpeningHours::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $openHours;

    /**
     * @var Collection<int, HolidayOpeningHours>
     */
    #[Groups(['resource', 'minimum'])]
    #[ORM\OneToMany(mappedBy: 'resource', targetEntity: HolidayOpeningHours::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $holidayOpenHours;

    /**
     * @var Collection<int, CvrWhitelist>
     */
    #[ORM\OneToMany(mappedBy: 'resource', targetEntity: CvrWhitelist::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $cvrWhitelists;

    #[Groups(['resource', 'minimum'])]
    #[ORM\Column(type: Types::STRING, length: 128, nullable: false)]
    private string $location;

    #[ORM\ManyToOne(targetEntity: Location::class, inversedBy: 'resources')]
    private ?Location $locationData = null;

    #[Groups(['resource', 'minimum'])]
    #[ORM\Column(type: Types::STRING, length: 128, nullable: false)]
    private string $resourceMail;

    #[Groups(['resource', 'minimum'])]
    #[ORM\Column(type: Types::STRING, length: 128, nullable: false)]
    private string $resourceName;

    #[Groups(['resource', 'minimum'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $resourceImage = null;

    #[Groups(['resource'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $resourceEmailText = null;

    #[Groups(['resource', 'minimum'])]
    #[ORM\Column(type: Types::STRING, length: 128, nullable: true)]
    private ?string $geoCoordinates = null;

    #[Groups(['resource', 'minimum'])]
    #[ORM\Column(type: Types::BIGINT, nullable: true)]
    private ?int $capacity = null;

    #[Groups(['resource', 'minimum'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $resourceDescription = null;

    #[Groups(['resource', 'minimum'])]
    #[ORM\Column(type: Types::BOOLEAN, nullable: false)]
    private bool $wheelchairAccessible;

    #[Groups(['resource', 'minimum'])]
    #[ORM\Column(type: Types::BOOLEAN, nullable: false)]
    private bool $videoConferenceEquipment;

    #[Groups(['resource', 'minimum'])]
    #[ORM\Column(type: Types::BOOLEAN, nullable: false)]
    private bool $monitorEquipment;

    #[Groups(['resource', 'minimum'])]
    #[ORM\Column(type: Types::BOOLEAN, nullable: false)]
    private bool $acceptanceFlow;

    #[Groups(['resource', 'minimum'])]
    #[ORM\Column(type: Types::BOOLEAN, nullable: false)]
    private bool $catering;

    #[Groups(['resource', 'minimum'])]
    #[ORM\Column(type: Types::BOOLEAN, nullable: false)]
    private bool $telecoil;

    #[Groups(['resource', 'minimum'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $formId = null;

    #[Groups(['resource', 'minimum'])]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $hasHolidayOpen = null;

    #[Groups(['resource', 'minimum'])]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $hasOpen = null;

    #[Groups(['resource', 'minimum'])]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $hasWhitelist = null;

    #[Groups(['resource'])]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $permissionEmployee = null;

    #[Groups(['resource'])]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $permissionCitizen = null;

    #[Groups(['resource'])]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $permissionBusinessPartner = null;

    #[Groups(['resource', 'minimum'])]
    #[ORM\Column(type: Types::STRING, length: 128, nullable: true)]
    private ?string $city = null;

    #[Groups(['resource', 'minimum'])]
    #[ORM\Column(type: Types::STRING, length: 128, nullable: true)]
    private ?string $streetName = null;

    #[Groups(['resource', 'minimum'])]
    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $postalCode = null;

    #[Groups(['resource', 'minimum'])]
    #[ORM\Column(type: Types::STRING, length: 128, nullable: true)]
    private ?string $resourceCategory = null;

    #[Groups(['resource', 'minimum'])]
    #[ORM\Column(type: Types::STRING, length: 128, nullable: true)]
    private ?string $resourceDisplayName = null;

    #[Groups(['resource', 'minimum'])]
    #[ORM\Column(type: Types::STRING, length: 128, nullable: true)]
    private ?string $locationDisplayName = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $acceptConflict = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $includeInUI = null;

    public function __construct()
    {
        $this->openHours = new ArrayCollection();
        $this->holidayOpenHours = new ArrayCollection();
    }

    public function getOpenHours(): Collection
    {
        return $this->openHours;
    }

    public function setOpenHours(Collection $openHours): void
    {
        $this->openHours = $openHours;
    }

    public function getHolidayOpenHours(): Collection
    {
        return $this->holidayOpenHours;
    }

    public function setHolidayOpenHours(Collection $holidayOpenHours): void
    {
        $this->holidayOpenHours = $holidayOpenHours;
    }

    public function getResourceMail(): string
    {
        return $this->resourceMail;
    }

    public function setResourceMail(string $resourceMail): void
    {
        $this->resourceMail = $resourceMail;
    }

    public function getResourceName(): string
    {
        return $this->resourceName;
    }

    public function setResourceName(string $resourceName): void
    {
        $this->resourceName = $resourceName;
    }

    public function getResourceImage(): ?string
    {
        return $this->resourceImage;
    }

    public function setResourceImage(?string $resourceImage): void
    {
        $this->resourceImage = $resourceImage;
    }

    public function getResourceEmailText(): ?string
    {
        return $this->resourceEmailText;
    }

    public function setResourceEmailText(?string $resourceEmailText): void
    {
        $this->resourceEmailText = $resourceEmailText;
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    public function setLocation(string $location): void
    {
        $this->location = $location;
    }

    public function getLocationData(): ?Location
    {
        return $this->locationData;
    }

    public function setLocationData(?Location $locationData): void
    {
        $this->locationData = $locationData;
    }

    public function getGeoCoordinates(): ?string
    {
        return $this->geoCoordinates;
    }

    public function setGeoCoordinates(?string $geoCoordinates): void
    {
        $this->geoCoordinates = $geoCoordinates;
    }

    public function getCapacity(): ?int
    {
        return $this->capacity;
    }

    public function setCapacity(?int $capacity): void
    {
        $this->capacity = $capacity;
    }

    public function getResourceDescription(): ?string
    {
        return $this->resourceDescription;
    }

    public function setResourceDescription(?string $resourceDescription): void
    {
        $this->resourceDescription = $resourceDescription;
    }

    public function isWheelchairAccessible(): bool
    {
        return $this->wheelchairAccessible;
    }

    public function setWheelchairAccessible(bool $wheelchairAccessible): void
    {
        $this->wheelchairAccessible = $wheelchairAccessible;
    }

    public function isVideoConferenceEquipment(): bool
    {
        return $this->videoConferenceEquipment;
    }

    public function setVideoConferenceEquipment(bool $videoConferenceEquipment): void
    {
        $this->videoConferenceEquipment = $videoConferenceEquipment;
    }

    public function isMonitorEquipment(): bool
    {
        return $this->monitorEquipment;
    }

    public function setMonitorEquipment(bool $monitorEquipment): void
    {
        $this->monitorEquipment = $monitorEquipment;
    }

    public function isAcceptanceFlow(): bool
    {
        return $this->acceptanceFlow;
    }

    public function setAcceptanceFlow(bool $acceptanceFlow): void
    {
        $this->acceptanceFlow = $acceptanceFlow;
    }

    public function isCatering(): bool
    {
        return $this->catering;
    }

    public function setCatering(bool $catering): void
    {
        $this->catering = $catering;
    }

    public function isTelecoil(): bool
    {
        return $this->telecoil;
    }

    public function setTelecoil(bool $telecoil): void
    {
        $this->telecoil = $telecoil;
    }

    public function getFormId(): ?string
    {
        return $this->formId;
    }

    public function setFormId(?string $formId): void
    {
        $this->formId = $formId;
    }

    public function getHasHolidayOpen(): ?bool
    {
        return $this->hasHolidayOpen;
    }

    public function setHasHolidayOpen(?bool $hasHolidayOpen): void
    {
        $this->hasHolidayOpen = $hasHolidayOpen;
    }

    public function getHasOpen(): ?bool
    {
        return $this->hasOpen;
    }

    public function setHasOpen(?bool $hasOpen): void
    {
        $this->hasOpen = $hasOpen;
    }

    public function getHasWhitelist(): ?bool
    {
        return $this->hasWhitelist;
    }

    public function setHasWhitelist(?bool $hasWhitelist): void
    {
        $this->hasWhitelist = $hasWhitelist;
    }

    public function getPermissionEmployee(): ?bool
    {
        return $this->permissionEmployee;
    }

    public function setPermissionEmployee(?bool $permissionEmployee): void
    {
        $this->permissionEmployee = $permissionEmployee;
    }

    public function getPermissionCitizen(): ?bool
    {
        return $this->permissionCitizen;
    }

    public function setPermissionCitizen(?bool $permissionCitizen): void
    {
        $this->permissionCitizen = $permissionCitizen;
    }

    public function getPermissionBusinessPartner(): ?bool
    {
        return $this->permissionBusinessPartner;
    }

    public function setPermissionBusinessPartner(?bool $permissionBusinessPartner): void
    {
        $this->permissionBusinessPartner = $permissionBusinessPartner;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): void
    {
        $this->city = $city;
    }

    public function getStreetName(): ?string
    {
        return $this->streetName;
    }

    public function setStreetName(?string $streetName): void
    {
        $this->streetName = $streetName;
    }

    public function getPostalCode(): ?int
    {
        return $this->postalCode;
    }

    public function setPostalCode(?int $postalCode): void
    {
        $this->postalCode = $postalCode;
    }

    public function getResourceCategory(): ?string
    {
        return $this->resourceCategory;
    }

    public function setResourceCategory(?string $resourceCategory): void
    {
        $this->resourceCategory = $resourceCategory;
    }

    public function getLocationDisplayName(): ?string
    {
        return $this->locationDisplayName;
    }

    public function setLocationDisplayName(?string $locationDisplayName): void
    {
        $this->locationDisplayName = $locationDisplayName;
    }

    public function getResourceDisplayName(): ?string
    {
        return $this->resourceDisplayName;
    }

    public function setResourceDisplayName(?string $resourceDisplayName): void
    {
        $this->resourceDisplayName = $resourceDisplayName;
    }

    public function getAcceptConflict(): ?bool
    {
        return $this->acceptConflict;
    }

    public function setAcceptConflict(?bool $acceptConflict): void
    {
        $this->acceptConflict = $acceptConflict;
    }

    public function getIncludeInUI(): ?bool
    {
        return $this->includeInUI;
    }

    public function setIncludeInUI(?bool $includeInUI): void
    {
        $this->includeInUI = $includeInUI;
    }
}
