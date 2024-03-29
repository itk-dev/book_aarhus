<?php

namespace App\DataProvider;

use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Entity\Main\Location;
use App\Repository\Resources\AAKResourceRepository;

final class LocationCollectionDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{
    public function __construct(
        private readonly AAKResourceRepository $AAKResourceRepository
    ) {
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return Location::class === $resourceClass;
    }

    public function getCollection(string $resourceClass, string $operationName = null, array $context = []): iterable
    {
        $whitelistKey = $context['filters']['whitelistKey'] ?? null;

        $locationNames = $this->AAKResourceRepository->findAllLocations($whitelistKey);

        foreach ($locationNames as $entry) {
            $location = new Location();
            $location->name = $entry['location'];

            yield $location;
        }
    }
}
