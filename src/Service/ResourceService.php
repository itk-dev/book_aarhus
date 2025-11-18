<?php

namespace App\Service;

use App\Entity\Main\CvrWhitelist;
use App\Entity\Main\HolidayOpeningHours;
use App\Entity\Main\Location;
use App\Entity\Main\OpeningHours;
use App\Entity\Main\Resource;
use App\Interface\ResourceServiceInterface;
use App\Repository\CvrWhitelistRepository;
use App\Repository\HolidayOpeningHoursRepository;
use App\Repository\LocationRepository;
use App\Repository\OpeningHoursRepository;
use App\Repository\ResourceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ResourceService implements ResourceServiceInterface
{
    public function __construct(
        private readonly ResourceRepository $resourceRepository,
        private readonly LocationRepository $locationRepository,
        private readonly OpeningHoursRepository $openingHoursRepository,
        private readonly HolidayOpeningHoursRepository $holidayOpeningHoursRepository,
        private readonly CacheInterface $resourceCache,
        private readonly SerializerInterface $serializer,
        private readonly MetricsHelper $metricsHelper,
        private readonly HttpClientInterface $client,
        private readonly EntityManagerInterface $entityManager,
        private readonly string $resourceListEndpoint,
        private readonly string $resourceLocationsEndpoint,
        private readonly string $resourceCvrWhitelistEndpoint,
        private readonly string $resourceOpeningHoursEndpoint,
        private readonly string $resourceHolidayOpeningHoursEndpoint,
        private readonly CvrWhitelistRepository $cvrWhitelistRepository,
        private readonly array $excludedResources,
    ) {
    }

    private function parseBoolString(string $boolString): bool
    {
        return 'True' === $boolString;
    }

    public function updateLocations(array $updatedLocations): void
    {
        $existingSourceIds = $this->locationRepository->getExistingSourceIds();

        $handledSourceIds = [];

        foreach ($updatedLocations as $locationData) {
            $locationId = $locationData['Location'];
            $location = $this->locationRepository->findOneBy(['location' => $locationId]);

            if (null === $location) {
                $location = new Location();
                $location->setLocation($locationId);
                $this->entityManager->persist($location);
            }

            $location->setDisplayName($locationData['LocationDisplayName']);
            $location->setAddress($locationData['Address']);
            $location->setCity($locationData['City']);
            $location->setPostalCode($locationData['PostalCode']);
            $location->setGeoCoordinates($locationData['GeoCoordinates']);

            $handledSourceIds[] = $locationId;
        }

        $sourceIdsToDelete = array_diff($existingSourceIds, $handledSourceIds);

        foreach ($sourceIdsToDelete as $sourceId) {
            $location = $this->locationRepository->findOneBy(['location' => $sourceId]);
            if (null !== $location) {
                // Unlink location from existing resources.
                $resourcesWithLocation = $this->resourceRepository->findBy(['location' => $location]);
                foreach ($resourcesWithLocation as $resource) {
                    $resource->setLocation(null);
                }

                $this->entityManager->remove($location);
            }
        }

        $this->entityManager->flush();
    }

    public function updateResources(array $updatedResources): void
    {
        $existingSourceIds = $this->resourceRepository->getExistingSourceIds();

        $handledSourceIds = [];

        foreach ($updatedResources as $resourceData) {
            $sourceId = $resourceData['ID'];
            $resource = $this->resourceRepository->findOneBy(['sourceId' => $resourceData['ID']]);

            if (null === $resource) {
                $resource = new Resource();
                $resource->setSourceId($sourceId);
                $this->entityManager->persist($resource);
            }

            $locationId = $resourceData['Location'];
            $location = $this->locationRepository->findOneBy(['location' => $locationId]);

            if (null !== $location) {
                $resource->setLocationData($location);
            }

            $resource->setResourceMail($resourceData['ResourceMail']);
            $resource->setResourceName($resourceData['ResourceName']);
            $resource->setResourceImage($resourceData['ResourceImage']);
            $resource->setResourceEmailText($resourceData['ResourceEmailText']);
            $resource->setResourceDescription($resourceData['ResourceDescription']);
            $resource->setCapacity((int) $resourceData['Capacity']);
            $resource->setWheelchairAccessible($this->parseBoolString($resourceData['WheelChairAccessible']));
            $resource->setVideoConferenceEquipment($this->parseBoolString($resourceData['VideoConferenceEquipment']));
            $resource->setMonitorEquipment($this->parseBoolString($resourceData['MonitorEquipment']));
            $resource->setAcceptanceFlow($this->parseBoolString($resourceData['AcceptanceFlow']));
            $resource->setCatering($this->parseBoolString($resourceData['Catering']));
            $resource->setTelecoil($this->parseBoolString($resourceData['Teleslynge']));
            $resource->setFormId($resourceData['FormID']);
            $resource->setHasHolidayOpen($this->parseBoolString($resourceData['HasHolidayOpen']));
            $resource->setHasOpen($this->parseBoolString($resourceData['HasOpen']));
            $resource->setHasWhitelist($this->parseBoolString($resourceData['HasWhiteList']));
            $resource->setPermissionEmployee($this->parseBoolString($resourceData['PermissionEmployee']));
            $resource->setPermissionCitizen($this->parseBoolString($resourceData['PermissionCitizen']));
            $resource->setPermissionBusinessPartner($this->parseBoolString($resourceData['PermissionBusinessPartner']));
            $resource->setIncludeInUI($this->parseBoolString($resourceData['IncludeinUI']));
            $resource->setAcceptConflict($this->parseBoolString($resourceData['AcceptConflict']));
            $resource->setResourceDisplayName($resourceData['ResourceDisplayName']);

            // Location fields.
            $resource->setLocation($location?->getLocation());
            $resource->setCity($location?->getCity());
            $resource->setPostalCode((int) $location?->getPostalCode());
            $resource->setGeoCoordinates($location?->getGeoCoordinates());
            $resource->setLocationDisplayName($location?->getDisplayName());
            $resource->setStreetName($location?->getAddress());

            $handledSourceIds[] = $resource->getSourceId();
        }

        $sourceIdsToDelete = array_diff($existingSourceIds, $handledSourceIds);

        foreach ($sourceIdsToDelete as $sourceId) {
            $resource = $this->resourceRepository->findOneBy(['sourceId' => $sourceId]);
            if (null !== $resource) {
                $this->entityManager->remove($resource);
            }
        }

        $this->entityManager->flush();
    }

    public function updateCVRWhitelists(array $updatedWhitelist): void
    {
        $existingSourceIds = $this->cvrWhitelistRepository->getExistingSourceIds();

        $handledSourceIds = [];

        foreach ($updatedWhitelist as $data) {
            $sourceId = (int) $data['ID'];
            $resourceId = (int) $data['resourceID'];
            $resource = $this->resourceRepository->findOneBy(['sourceId' => $resourceId]);

            if (null === $resource) {
                continue;
            }

            $entry = $this->cvrWhitelistRepository->findOneBy(['sourceId' => $sourceId]);

            if (null === $entry) {
                $entry = new CvrWhitelist();
                $entry->setSourceId($sourceId);
                $this->entityManager->persist($entry);
            }

            $entry->setCvr($data['cvr']);
            $entry->setResource($resource);

            $handledSourceIds[] = $entry->getSourceId();
        }

        $sourceIdsToDelete = array_diff($existingSourceIds, $handledSourceIds);

        foreach ($sourceIdsToDelete as $sourceId) {
            $entry = $this->cvrWhitelistRepository->findOneBy(['sourceId' => $sourceId]);
            if (null !== $entry) {
                $this->entityManager->remove($entry);
            }
        }

        $this->entityManager->flush();
    }

    /**
     * Updates resources from external API endpoints.
     */
    public function update(): void
    {
        $responseLocations = $this->client->request('GET', $this->resourceLocationsEndpoint);
        $locationsFromEndpoint = $responseLocations->toArray();
        $this->updateLocations($locationsFromEndpoint);

        $responseResources = $this->client->request('GET', $this->resourceListEndpoint);
        $resourcesFromEndpoint = $responseResources->toArray();
        $this->updateResources($resourcesFromEndpoint);

        $responseCvrWhitelist = $this->client->request('GET', $this->resourceCvrWhitelistEndpoint);
        $cvrWhitelistFromEndpoint = $responseCvrWhitelist->toArray();
        $this->updateCVRWhitelists($cvrWhitelistFromEndpoint);

        $response = $this->client->request('GET', $this->resourceOpeningHoursEndpoint);
        $openingHoursFromEndpoint = $response->toArray();
        $this->updateOpeningHours($openingHoursFromEndpoint);

        $response = $this->client->request('GET', $this->resourceHolidayOpeningHoursEndpoint);
        $holidayOpeningHoursFromEndpoint = $response->toArray();
        $this->updateHolidayOpeningHours($holidayOpeningHoursFromEndpoint);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function removeResourcesCacheEntry(?string $permission = null): void
    {
        $this->resourceCache->delete("resources-$permission");
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getAllResources(?string $permission = null, int $cacheLifetime = 60 * 30): array
    {
        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::INVOKE);

        $cachedResources = $this->resourceCache->get("resources-$permission", function (CacheItemInterface $cacheItem) use ($cacheLifetime, $permission) {
            $cacheItem->expiresAfter($cacheLifetime);
            $info = $this->resourceRepository->getAllByPermission($permission, true, $this->excludedResources);

            return $this->serializer->serialize($info, 'json', ['groups' => 'minimum']);
        });

        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::COMPLETE);

        return json_decode($cachedResources);
    }

    public function getWhitelistedResources($permission, $whitelistKey): array
    {
        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::INVOKE);

        $info = $this->resourceRepository->getOnlyWhitelisted($permission, $whitelistKey, $this->excludedResources);

        $serializedWhitelistedResources = $this->serializer->serialize($info, 'json', ['groups' => 'minimum']);

        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::COMPLETE);

        return json_decode($serializedWhitelistedResources);
    }

    public function updateOpeningHours(array $updatedOpeningHours)
    {
        $existingSourceIds = $this->openingHoursRepository->getExistingSourceIds();

        $handledSourceIds = [];

        foreach ($updatedOpeningHours as $data) {
            $resourceId = (int) $data['resourceID'];
            $sourceId = (int) $data['ID'];

            $resource = $this->resourceRepository->findOneBy(['sourceId' => $resourceId]);

            if (null === $resource) {
                continue;
            }

            $entry = $this->openingHoursRepository->findOneBy(['sourceId' => $sourceId]);

            if (null === $entry) {
                $entry = new OpeningHours();
                $entry->setSourceId($sourceId);
                $this->entityManager->persist($entry);
            }

            $entry->setResource($resource);
            $entry->setWeekday((int) $data['weekday']);
            $entry->setOpen(\DateTime::createFromFormat('H:i:s', $data['open']));
            $entry->setClose(\DateTime::createFromFormat('H:i:s', $data['close']));

            $handledSourceIds[] = $entry->getSourceId();
        }

        $sourceIdsToDelete = array_diff($existingSourceIds, $handledSourceIds);

        foreach ($sourceIdsToDelete as $sourceId) {
            $entry = $this->openingHoursRepository->findOneBy(['sourceId' => $sourceId]);
            if (null !== $entry) {
                $this->entityManager->remove($entry);
            }
        }

        $this->entityManager->flush();
    }

    public function updateHolidayOpeningHours(array $updatedHolidayOpeningHours)
    {
        $existingSourceIds = $this->holidayOpeningHoursRepository->getExistingSourceIds();

        $handledSourceIds = [];

        foreach ($updatedHolidayOpeningHours as $data) {
            $resourceId = (int) $data['resourceID'];
            $sourceId = (int) $data['ID'];

            $resource = $this->resourceRepository->findOneBy(['sourceId' => $resourceId]);

            if (null === $resource) {
                continue;
            }

            $entry = $this->holidayOpeningHoursRepository->findOneBy(['sourceId' => $sourceId]);

            if (null === $entry) {
                $entry = new HolidayOpeningHours();
                $entry->setSourceId($sourceId);
                $this->entityManager->persist($entry);
            }

            $entry->setResource($resource);
            $entry->setHolidayOpen(\DateTime::createFromFormat('H:i:s', $data['holidayopen']));
            $entry->setHolidayClose(\DateTime::createFromFormat('H:i:s', $data['holidayclose']));

            $handledSourceIds[] = $entry->getSourceId();
        }

        $sourceIdsToDelete = array_diff($existingSourceIds, $handledSourceIds);

        foreach ($sourceIdsToDelete as $sourceId) {
            $entry = $this->holidayOpeningHoursRepository->findOneBy(['sourceId' => $sourceId]);
            if (null !== $entry) {
                $this->entityManager->remove($entry);
            }
        }

        $this->entityManager->flush();
    }
}
