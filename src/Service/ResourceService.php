<?php

namespace App\Service;

use App\Entity\Main\CvrWhitelist;
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
        private readonly string $resourceOpenHoursEndpoint,
        private readonly string $resourceHolidayOpenHoursEndpoint, private readonly CvrWhitelistRepository $cvrWhitelistRepository,
    ) {
    }

    private function parseBoolString(string $boolString): bool
    {
        return $boolString === 'True';
    }

    public function updateResources(): array
    {
        $responseLocations = $this->client->request('GET', $this->resourceLocationsEndpoint);
        $locationsFromEndpoint = $responseLocations->toArray();

        echo '1';

        foreach ($locationsFromEndpoint as $locationData) {
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
        }

        $this->entityManager->flush();

        $responseResources = $this->client->request('GET', $this->resourceListEndpoint);
        $resourcesFromEndpoint = $responseResources->toArray();

        echo '3';

        foreach ($resourcesFromEndpoint as $resourceData) {
            $resource = $this->resourceRepository->findOneBy(['sourceId' => $resourceData['ID']]);

            if (null === $resource) {
                $resource = new Resource();
                $this->entityManager->persist($resource);
            }

            $resource->setSourceId($resourceData['ID']);
            $resource->setResourceMail($resourceData['ResourceMail']);
            $resource->setResourceName($resourceData['ResourceName']);
            $resource->setResourceImage($resourceData['ResourceImage']);
            $resource->setResourceEmailText($resourceData['ResourceEmailText']);
            $resource->setGeoCoordinates($resourceData['GeoCordinates']);
            $resource->setCapacity((int) $resourceData['Capacity']);
            $resource->setResourceDescription($resourceData['ResourceDescription']);
            $resource->setWheelchairAccessible($this->parseBoolString($resourceData['WheelChairAccessible']));
            $resource->setVideoConferenceEquipment($this->parseBoolString($resourceData['VideoConferenceEquipment']));
            $resource->setMonitorEquipment($this->parseBoolString($resourceData['MonitorEquipment']));
            $resource->setAcceptanceFlow($this->parseBoolString($resourceData['AcceptanceFlow']));
            $resource->setCatering($this->parseBoolString($resourceData['Catering']));
            $resource->setFormId($resourceData['FormID']);
            $resource->setHasHolidayOpen($this->parseBoolString($resourceData['HasHolidayOpen']));
            $resource->setHasOpen($this->parseBoolString($resourceData['HasOpen']));
            $resource->setHasWhitelist($this->parseBoolString($resourceData['HasWhiteList']));
            $resource->setPermissionEmployee($this->parseBoolString($resourceData['PermissionEmployee']));
            $resource->setPermissionCitizen($this->parseBoolString($resourceData['PermissionCitizen']));
            $resource->setPermissionBusinessPartner($this->parseBoolString($resourceData['PermissionBusinessPartner']));
            $resource->setDisplayName($resourceData['DisplayName']);
            $resource->setCity($resourceData['City']);
            $resource->setPostalCode((int) $resourceData['PostalCode']);
            $resource->setResourceDisplayName($resourceData['ResourceDisplayName']);
            $resource->setLocationDisplayName($resourceData['LocationDisplayName']);
            $resource->setAcceptConflict($this->parseBoolString($resourceData['AcceptConflict']));
            $resource->setIncludeInUI($this->parseBoolString($resourceData['IncludeinUI']));
        }

        $this->entityManager->flush();

        $responseCvrWhitelist = $this->client->request('GET', $this->resourceCvrWhitelistEndpoint);
        $cvrWhitelistFromEndpoint = $responseCvrWhitelist->toArray();

        echo '2';

        // TODO: Get list of existing resources.
        // Remove those that do not exist in external list.

        foreach ($cvrWhitelistFromEndpoint as $data) {
            $resourceId = (int) $data['resourceID'];
            $resource = $this->resourceRepository->findOneBy(['sourceId' => $resourceId]);

            if ($resource === null) {
                continue;
            }

            /** @var CvrWhitelist $entry */
            $entry = $this->cvrWhitelistRepository->findOneBy(['sourceId' => $data['ID']]);

            if (null === $entry) {
                $entry = new CvrWhitelist();
                $entry->setSourceId((int) $data['ID']);
                $this->entityManager->persist($entry);
            }

            $entry->setCvr($data['cvr']);
            $entry->setResource($resource);
        }

        $this->entityManager->flush();

        echo '4';
/*
        $responseOpenHours = $this->client->request('GET', $this->resourceOpenHoursEndpoint);
        $openHoursFromEndpoint = $responseOpenHours->toArray();

        foreach ($openHoursFromEndpoint as $openHoursData) {
            $openingHours = $this->openingHoursRepository->findOneBy(['sourceId' => $openHoursData['ID']]);

            if (null === $openingHours) {
                $openingHours = new OpeningHours();
                $openingHours->setSourceId($openHoursData['ID']);
                $this->entityManager->persist($openingHours);
            }

            $openingHours->setResourceId($openHoursData['resourceID']);
            $openingHours->setWeekday((int) $openHoursData['weekday']);
            $openingHours->setOpenTime(\DateTime::createFromFormat('H:i:s', $openHoursData['open']));
            $openingHours->setOpenTime(\DateTime::createFromFormat('H:i:s', $openHoursData['close']));
        }

        $responseHolidayOpenHours = $this->client->request('GET', $this->resourceHolidayOpenHoursEndpoint);
        $holidayOpenHoursFromEndpoint = $responseHolidayOpenHours->toArray();
*/
        return [];
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
            $info = $this->resourceRepository->getAllByPermission($permission, true);

            return $this->serializer->serialize($info, 'json', ['groups' => 'minimum']);
        });

        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::COMPLETE);

        return json_decode($cachedResources);
    }

    public function getWhitelistedResources($permission, $whitelistKey): array
    {
        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::INVOKE);

        $info = $this->resourceRepository->getOnlyWhitelisted($permission, $whitelistKey);
        $serializedWhitelistedResources = $this->serializer->serialize($info, 'json', ['groups' => 'minimum']);

        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::COMPLETE);

        return json_decode($serializedWhitelistedResources);
    }
}
