<?php

namespace App\Service;

use App\Entity\Main\UserBooking;
use App\Entity\Main\UserBookingCacheEntry;
use App\Exception\MicrosoftGraphCommunicationException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * @see https://github.com/microsoftgraph/msgraph-sdk-php
 * @see https://docs.microsoft.com/en-us/graph/use-the-api
 */
class UserBookingCacheService implements UserBookingCacheServiceInterface
{
    public function __construct(
    private readonly MicrosoftGraphHelperService $graphHelperService,
    private readonly EntityManagerInterface $entityManager,
    private readonly MicrosoftGraphBookingService $microsoftGraphBookingService,
    private readonly LoggerInterface $logger,
  ) {
    }

    /**
     * {@inheritdoc}
     *
     * @throws \App\Exception\MicrosoftGraphCommunicationException
     */
    public function rebuildCache(): void
    {
        try {
            $this->clearUserBookingCache();
            $token = $this->graphHelperService->authenticateAsServiceAccount();
            $now = new \DateTime('now');
            $nowFormatted = $now->setTimezone(new \DateTimeZone('UTC'))->format(MicrosoftGraphBookingService::DATE_FORMAT).'Z';

            $query = implode('&', [
                "\$filter=end/dateTime gt '$nowFormatted'",
                '$top=100',
            ]
            );
            $response = $this->graphHelperService->request("/me/events?$query", $token);
            $nextResponse = $response;

            // Loop over all pages of request.
            while (isset($nextResponse)) {
                $resultBody = $nextResponse->getBody();

                // Loop over all elements on page.
                foreach ($resultBody['value'] as $booking) {
                    try {
                        $userBooking = $this->microsoftGraphBookingService->getUserBookingFromApiData($booking);
                        $this->entityManager->persist($this->createCacheEntity($userBooking));
                    } catch (\Exception $e) {
                        $this->logger->error($e->getMessage());
                    }
                }

                // Determine next page.
                if (isset($resultBody['@odata.nextLink'])) {
                    $nextQuery = strstr($resultBody['@odata.nextLink'], '/me/events');
                    $nextResponse = $this->graphHelperService->request($nextQuery, $token);
                } else {
                    $nextResponse = null;
                }
            }

            // Add elements to db.
            $this->entityManager->flush();
        } catch (\Exception $e) {
            throw new MicrosoftGraphCommunicationException($e->getMessage(), (int) $e->getCode());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addCacheEntry(UserBooking $userBooking): void
    {
        $this->entityManager->persist($this->createCacheEntity($userBooking));
        $this->entityManager->flush();
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function changeCacheEntry(string $exchangeId, array $changes): void
    {
        $entity = $this->entityManager->getRepository(UserBookingCacheEntry::class)
          ->findOneBy(['exchangeId' => $exchangeId]);

        if (!$entity) {
            throw new \Exception('No cache entry found for exchangeId: '. $exchangeId);
        }

        foreach ($changes as $field => $value) {
            $field = ucfirst($field);
            $methodName = "set{$field}";

            // check method
            if (!\method_exists($entity, $methodName)) {
                throw new \Exception($methodName.' not found.');
            }

            $entity->{$methodName}($value);
        }

        $this->entityManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteCacheEntry(string $exchangeId): void
    {
      $entity = $this->entityManager->getRepository(UserBookingCacheEntry::class)
        ->findOneBy(['exchangeId' => $exchangeId]);
      if ($entity) {
        $this->entityManager->remove($entity);
      }

    }

    /**
     * Create cache entity.
     *
     * @param UserBooking $userBooking
     *
     * @return \App\Entity\Main\UserBookingCacheEntry
     */
    private function createCacheEntity(UserBooking $userBooking): UserBookingCacheEntry
    {
        $cacheEntry = new UserBookingCacheEntry();
        if ($userBooking->resourceMail) {
            $cacheEntry->setTitle($userBooking->subject);
            $cacheEntry->setExchangeId($userBooking->id);
            $cacheEntry->setUid($this->retrieveUidFromBody($userBooking->body) ?? '');
            $cacheEntry->setStart($userBooking->start);
            $cacheEntry->setEnd($userBooking->end);
            $cacheEntry->setStatus($userBooking->status);
            $cacheEntry->setResource($userBooking->resourceMail);
        }

        return $cacheEntry;
    }

    /**
     * Get uid from mail body.
     *
     * @param string $body
     *
     * @return string|null
     */
    private function retrieveUidFromBody(string $body): ?string
    {
        $doc = new \DOMDocument();
        $doc->loadHTML($body);
        $uidDomElement = $doc->getElementById('userId');

        return $this->extractRealUid($uidDomElement?->textContent);
    }

    /**
     * Clear UserBookingCacheEntry table.
     *
     * @return void
     */
    private function clearUserBookingCache(): void
    {
        $repository = $this->entityManager->getRepository(UserBookingCacheEntry::class);
        $entities = $repository->findAll();

        foreach ($entities as $entity) {
            $this->entityManager->remove($entity);
        }
    }

  /**
   * Remove UID from front and back of id.
   *
   * @param $documentBodyUid
   *
   * @return string
   */
    private function extractRealUid($documentBodyUid): string {
      $documentBodyUid = ltrim($documentBodyUid, 'UID-');

      return rtrim($documentBodyUid, '-UID');
    }
}
