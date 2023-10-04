<?php

namespace App\Service;

use App\Entity\Main\UserBooking;
use App\Entity\Main\UserBookingCacheEntry;
use App\Exception\MicrosoftGraphCommunicationException;
use App\Exception\UserBookingException;
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
            $this->updateCache(false);
        } catch (\Exception $e) {
            throw new MicrosoftGraphCommunicationException($e->getMessage(), (int) $e->getCode());
        }
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     *
     * @throws \App\Exception\MicrosoftGraphCommunicationException
     */
    public function updateCache($removeOutdated = true): void
    {
        try {
            $token = $this->graphHelperService->authenticateAsServiceAccount();
            $nextResponse = $this->microsoftGraphBookingService->getAllFutureBookings($token);
            $exchangeBookings = [];

            // Loop over all pages of request.
            while (isset($nextResponse)) {
                $resultBody = $nextResponse->getBody();

                // Loop over all elements on page.
                foreach ($resultBody['value'] as $booking) {
                    $exchangeBookings[] = $booking['id'];
                    try {
                        $userBooking = $this->microsoftGraphBookingService->getUserBookingFromApiData($booking);
                        $entity = $this->entityManager->getRepository(UserBookingCacheEntry::class)
                            ->findOneBy(['exchangeId' => $userBooking->id]);

                        if (null === $entity) {
                            $entity = new UserBookingCacheEntry();
                        }

                        $this->entityManager->persist($this->setCacheEntityValues($entity, $userBooking));
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

            if ($removeOutdated) {
                $this->removeOutdatedEntries($exchangeBookings);
            }

            $this->entityManager->flush();
        } catch (UserBookingException $e) {
        } catch (\Exception $e) {
            throw new MicrosoftGraphCommunicationException($e->getMessage(), (int) $e->getCode());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addCacheEntry(UserBooking $userBooking): void
    {
        $entity = new UserBookingCacheEntry();
        $this->entityManager->persist($this->setCacheEntityValues($entity, $userBooking));
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
            throw new \Exception('No cache entry found for exchangeId: '.$exchangeId);
        }

        foreach ($changes as $field => $value) {
            switch ($field) {
                case 'title':
                    $entity->setTitle($value);
                    break;
                case 'uid':
                    break;
                case 'start':
                    $entity->setStart($value);

                    break;
                case 'end':
                    $entity->setEnd($value);
                    break;
                case 'status':
                    $entity->setStatus($value);
                    break;
                case 'resource':
                    $entity->setResource($value);
                    break;
            }
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
     * Set values for cache entity.
     *
     * @param $entity
     * @param \App\Entity\Main\UserBooking $userBooking
     *
     * @return \App\Entity\Main\UserBookingCacheEntry
     */
    private function setCacheEntityValues($entity, UserBooking $userBooking): UserBookingCacheEntry
    {
        if ($userBooking->resourceMail) {
            $entity->setTitle($userBooking->subject);
            $entity->setExchangeId($userBooking->id);
            $entity->setUid($this->retrieveUidFromBody($userBooking->body) ?? '');
            $entity->setStart($userBooking->start);
            $entity->setEnd($userBooking->end);
            $entity->setStatus($userBooking->status);
            $entity->setResource($userBooking->resourceMail);
        }

        return $entity;
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

        $this->entityManager->flush();
    }

    /**
     * Remove UID from front and back of id.
     *
     * @param $documentBodyUid
     *
     * @return string
     */
    private function extractRealUid($documentBodyUid): string
    {
        $documentBodyUid = ltrim($documentBodyUid, 'UID-');

        return rtrim($documentBodyUid, '-UID');
    }

    /**
     * Remove outdated entries in cache.
     *
     * @param $exchangeBookings
     */
    private function removeOutdatedEntries($exchangeBookings): void
    {
        $repository = $this->entityManager->getRepository(UserBookingCacheEntry::class);
        $entities = $repository->findAll();

        foreach ($entities as $entity) {
            if (!in_array($entity->getExchangeId(), $exchangeBookings)) {
                $this->entityManager->remove($entity);
            }
        }
        $this->entityManager->flush();
    }
}
