<?php

namespace App\Service;

/**
 * @see https://github.com/microsoftgraph/msgraph-sdk-php
 * @see https://docs.microsoft.com/en-us/graph/use-the-api
 */
interface UserBookingCacheServiceInterface {

  /**
   * Rebuild the user bookings cache.
   *
   * @return void
   */
  public function rebuildCache() : void;

  /**
   * Add an entry to the cache table.
   *
   * @param array $data the booking data to add to cache.
   *
   * @return void
   */
  public function addCacheEntry(array $data) : void;

  /**
   * Change an entry in the cache table.
   *
   * @return void
   */
  public function changeCacheEntry() : void;

  /**
   * Delete an entry from the cache table.
   *
   * @return void
   */
  public function deleteCacheEntry() : void;
}