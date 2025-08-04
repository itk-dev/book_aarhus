<?php

namespace App\Interface;

use App\Entity\Api\UserBooking;

/**
 * @see https://github.com/microsoftgraph/msgraph-sdk-php
 * @see https://docs.microsoft.com/en-us/graph/use-the-api
 */
interface UserBookingCacheServiceInterface
{
    /**
     * Rebuild the user bookings cache.
     */
    public function rebuildCache(): void;

    /**
     * Update the user bookings cache.
     */
    public function updateCache(): void;

    /**
     * Add an entry to the cache table.
     *
     * @param UserBooking $userBooking
     *                                 A user booking to add to cache
     */
    public function addCacheEntry(UserBooking $userBooking): void;

    /**
     * Add a cache entry from array data.
     */
    public function addCacheEntryFromArray(array $data): void;

    /**
     * Change an entry in the cache table.
     *
     * @param string $exchangeId
     *                           The id of the entity
     * @param array  $changes
     *                           An array of the changes to make [DB_FIELD, CHANGE]
     */
    public function changeCacheEntry(string $exchangeId, array $changes): void;

    /**
     * Delete an entry from the cache table.
     */
    public function deleteCacheEntry(string $exchangeId): void;

    /**
     * Delete an entry from the cache table.
     */
    public function deleteCacheEntryByICalUId(string $iCalUId): void;
}
