<?php

namespace App\Interface;

use App\Entity\Main\UserBooking;

/**
 * @see https://github.com/microsoftgraph/msgraph-sdk-php
 * @see https://docs.microsoft.com/en-us/graph/use-the-api
 */
interface UserBookingCacheServiceInterface
{
    /**
     * Rebuild the user bookings cache.
     *
     * @return void
     */
    public function rebuildCache(): void;

    /**
     * Update the user bookings cache.
     *
     * @return void
     */
    public function updateCache(): void;

    /**
     * Add an entry to the cache table.
     *
     * @param UserBooking $userBooking
     *   A user booking to add to cache
     *
     * @return void
     */
    public function addCacheEntry(UserBooking $userBooking): void;

    /**
     * Add a cache entry from array data.
     *
     * @param array $data
     *
     * @return void
     */
    public function addCacheEntryFromArray(array $data): void;

    /**
     * Change an entry in the cache table.
     *
     * @param string $exchangeId
     *   The id of the entity
     * @param array $changes
     *   An array of the changes to make [DB_FIELD, CHANGE]
     *
     * @return void
     */
    public function changeCacheEntry(string $exchangeId, array $changes): void;

    /**
     * Delete an entry from the cache table.
     *
     * @param string $exchangeId
     *
     * @return void
     */
    public function deleteCacheEntry(string $exchangeId): void;

    /**
     * Delete an entry from the cache table.
     *
     * @param string $iCalUId
     *
     * @return void
     */
    public function deleteCacheEntryByICalUId(string $iCalUId): void;
}
