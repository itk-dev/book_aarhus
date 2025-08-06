<?php

namespace App\Interface;

interface ResourceServiceInterface
{
    /**
     * Remove resources cache entry.
     *
     * NULL for -
     * citizen for -citizen
     * businessPartner for -businessPartner
     *
     * @param string|null $permission The name of the permission for the cache entry to remove
     */
    public function removeResourcesCacheEntry(?string $permission = null): void;

    /**
     * Get all resources.
     *
     * @param string|null $permission    the name of the permission to retrieve resources for
     * @param int         $cacheLifetime Cache lifetime in seconds
     *
     * @return array Array of Resource
     */
    public function getAllResources(?string $permission = null, int $cacheLifetime = 1800): array;

    /**
     * Get whitelisted resources.
     *
     * @param string $permission   the name of the permission to retrieve resources for
     * @param string $whitelistKey the whitelist key
     *
     * @return array Array of Resource
     */
    public function getWhitelistedResources(string $permission, string $whitelistKey): array;
}
