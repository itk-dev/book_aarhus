<?php

namespace App\Service;

use App\Entity\Main\UserBookingCacheEntry;
use App\Exception\MicrosoftGraphCommunicationException;
use DateTime;
use DOMDocument;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @see https://github.com/microsoftgraph/msgraph-sdk-php
 * @see https://docs.microsoft.com/en-us/graph/use-the-api
 */
class UserBookingCacheService implements UserBookingCacheServiceInterface {

  public function __construct(
    private readonly MicrosoftGraphHelperService $graphHelperService,
    private readonly EntityManagerInterface $entityManager,
  ) {
  }

  /**
   * {@inheritdoc}
   *
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

      // Loop over all pages of request.
      while (isset($nextResponse)) {
        $resultBody = $nextResponse->getBody();

        // Loop over all elements on page.
        foreach ($resultBody['value'] as $booking) {
          $bookingAsCacheEntry = $this->prepareCacheEntry($booking);
          $this->entityManager->persist($this->createCacheEntity($bookingAsCacheEntry));
        }

        // Determine next page.
        if (isset($resultBody['@odata.nextLink'])) {
          $nextQuery = strstr($resultBody['@odata.nextLink'], '/me/events');
          $nextResponse = $this->graphHelperService->request($nextQuery, $token);
        }
        else {
          $nextResponse = null;
        }
      }

      // Add elements to db.
      $this->entityManager->flush();
    }
    catch (\Exception $e) {
      throw new MicrosoftGraphCommunicationException($e->getMessage(), (int) $e->getCode());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function addCacheEntry(array $data): void {
    $bookingAsCacheEntry = $this->prepareCacheEntry($data);
    $this->entityManager->persist($this->createCacheEntity($bookingAsCacheEntry));
    $this->entityManager->flush();
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


  /**
   * Create cache entity.
   *
   * @param array $data
   *
   * @return \App\Entity\Main\UserBookingCacheEntry
   */
  private function createCacheEntity(array $data): UserBookingCacheEntry {
    $cacheEntry = new UserBookingCacheEntry();

    $cacheEntry->setTitle($data['title']);
    $cacheEntry->setExchangeId($data['exchange_id']);
    $cacheEntry->setUid($data['uid']);
    $cacheEntry->setStart($data['start']);
    $cacheEntry->setEnd($data['end']);
    $cacheEntry->setStatus($data['status']);
    $cacheEntry->setResource($data['resource']);

    return $cacheEntry;
  }

  /**
   * Prepare a cache entry.
   *
   * @param array $booking
   *
   * @return array
   */
  private function prepareCacheEntry(array $booking): array {
    return [
      'title' => $booking['subject'],
      'exchange_id' => $booking['id'],
      'uid' => $this->retrieveUidFromBody($booking['body']) ?? '',
      'start' => DateTime::createFromFormat("Y-m-d\TH:i:s.0000000", $booking['start']['dateTime']),
      'end' => DateTime::createFromFormat("Y-m-d\TH:i:s.0000000", $booking['end']['dateTime']),
      'status' => $booking['responseStatus']['response'],
      'resource' => $booking['organizer']['emailAddress']['address']
    ];
  }

  /**
   * Get uid from mail body.
   *
   * @param array $body
   *
   * @return string|null
   */
  private function retrieveUidFromBody(array $body): ?string {
    $doc = new DOMDocument();
    $doc->loadHTML($body['content']);
    $uidDomElement = $doc->getElementById('userId');

    return $uidDomElement?->textContent;
  }

}