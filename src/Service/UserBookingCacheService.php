<?php

namespace App\Service;

use App\Exception\MicrosoftGraphCommunicationException;
use App\Exception\UserBookingException;
use DateTime;
use Psr\Log\LoggerInterface;

/**
 * @see https://github.com/microsoftgraph/msgraph-sdk-php
 * @see https://docs.microsoft.com/en-us/graph/use-the-api
 */
class UserBookingCacheService implements UserBookingCacheServiceInterface {

  public function __construct(
    private readonly MicrosoftGraphHelperService $graphHelperService,
  ) {
  }

  /**
   * {@inheritdoc}
   * @throws \App\Exception\MicrosoftGraphCommunicationException
   */
  public function rebuildCache(): void {
    try {
      $token = $this->graphHelperService->authenticateAsServiceAccount();
      $now = new DateTime('now');
      $nowFormatted = $now->setTimezone(new \DateTimeZone('UTC'))->format(MicrosoftGraphBookingService::DATE_FORMAT).'Z';

      $query = implode('&', [
          "\$filter=end/dateTime gt '$nowFormatted'",
          "\$top=100"
        ]
      );
      $response = $this->graphHelperService->request("/me/events?$query", $token);
      $nextResponse = $response;

      while (isset($nextResponse)) {
        $resultBody = $nextResponse->getBody();

        foreach ($resultBody['value'] as $booking) {
          $bookingAsCacheEntry = $this->prepareCacheEntry($booking);
          $this->addCacheEntry($bookingAsCacheEntry);
        }

        if (isset($resultBody['@odata.nextLink'])) {
          $nextQuery = strstr($resultBody['@odata.nextLink'], '/me/events');
          $nextResponse = $this->graphHelperService->request($nextQuery, $token);
        }
        else {
          $nextResponse = null;
        }
      }
    }
    catch (\Exception $e) {
      throw new MicrosoftGraphCommunicationException($e->getMessage(), (int) $e->getCode());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function addCacheEntry(array $data): void {
    // Create database entry from parameters.
  }

  /**
   * {@inheritdoc}
   */
  public function changeCacheEntry(): void {
    // Change database entry from parameters.

    // Use queue to change booking through microsoft graph. ???
  }

  /**
   * {@inheritdoc}
   */
  public function deleteCacheEntry(): void {
    // Delete cache entry from parameter
  }

  private function prepareCacheEntry(array $booking): array {
    $a =1;
    return [
      'id' => $booking['id'],
      'title' => $booking['subject'],
      'uid' => '',
      'start' => $booking['start']['dateTime'],
      'end' => $booking['end']['dateTime'],
      'status' => '',
      'resource' => $booking['organizer']['emailAddress']['address']
    ];
  }
}