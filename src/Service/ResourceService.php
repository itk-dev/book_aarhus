<?php

namespace App\Service;

use App\Repository\Resources\AAKResourceRepository;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\CacheInterface;

class ResourceService implements ResourceServiceInterface
{
    public function __construct(
        private readonly AAKResourceRepository $aakResourceRepository,
        private readonly CacheInterface $resourceCache,
        private readonly SerializerInterface $serializer,
    ) {
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
        $cachedResources = $this->resourceCache->get("resources-$permission", function (CacheItemInterface $cacheItem) use ($cacheLifetime, $permission) {
            $cacheItem->expiresAfter($cacheLifetime);
            $info = $this->aakResourceRepository->getAllByPermission($permission);

            return $this->serializer->serialize($info, 'json', ['groups' => 'minimum']);
        });

        return json_decode($cachedResources);
    }

    public function getWhitelistedResources($permission, $whitelistKey): array
    {
        $info = $this->aakResourceRepository->getOnlyWhitelisted($permission, $whitelistKey);
        $serializedWhitelistedResources = $this->serializer->serialize($info, 'json', ['groups' => 'minimum']);

        return json_decode($serializedWhitelistedResources);
    }
}
