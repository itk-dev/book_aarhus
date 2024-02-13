<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Main\Location;
use App\Repository\Resources\AAKResourceRepository;

class LocationCollectionProvider implements ProviderInterface
{
    public function __construct(private readonly AAKResourceRepository $AAKResourceRepository)
    {

    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return Location::class === $resourceClass;
    }


    public function provide(Operation $operation, array $uriVariables = [],  array $context = []): iterable
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
