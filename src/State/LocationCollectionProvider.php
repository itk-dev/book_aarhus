<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Main\Location;
use App\Repository\Resources\AAKResourceRepository;

/**
 * @template-implements ProviderInterface<object>
 */
class LocationCollectionProvider implements ProviderInterface
{
    public function __construct(private readonly AAKResourceRepository $AAKResourceRepository)
    {
    }

    public function supports(string $resourceClass): bool
    {
        return Location::class === $resourceClass;
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $whitelistKey = $context['filters']['whitelistKey'] ?? null;

        $locationNames = $this->AAKResourceRepository->findAllLocations($whitelistKey);

        $result = [];

        foreach ($locationNames as $entry) {
            $location = new Location();
            $location->name = $entry['location'];

            $result[] = $location;
        }

        return $result;
    }
}
