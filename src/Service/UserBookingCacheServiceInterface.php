<?php

namespace App\Service;

use App\Entity\Main\UserBooking;
use App\Entity\Main\UserBookingCacheEntry;

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
     * Add an entry to the cache table.
     *
     * @param UserBooking $userBooking a user booking to add to cache
     *
     * @return void
     */
    public function addCacheEntry(UserBooking $userBooking): void;

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
     * Get user bookings
     *
     * @param string $userId
     * @param array $filter
     * @param int $page
     * @param int $pageSize
     *
     * @return array
     */
    public function getUserCachedBookings(string $userId, array $filter = [], int $page = 0, int $pageSize = 25): array;

}
