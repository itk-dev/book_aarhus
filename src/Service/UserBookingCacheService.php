<?php

namespace App\Service;

use App\Entity\Api\UserBooking;
use App\Entity\Main\Resource;
use App\Entity\Main\UserBookingCacheEntry;
use App\Exception\MicrosoftGraphCommunicationException;
use App\Interface\UserBookingCacheServiceInterface;
use App\Repository\ResourceRepository;
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
        private readonly ResourceRepository $resourceRepository,
        private readonly MetricsHelper $metricsHelper,
    ) {
    }

    /**
     * {@inheritdoc}
     *
     * @throws MicrosoftGraphCommunicationException
     */
    public function rebuildCache(): void
    {
        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::INVOKE);

        try {
            $this->clearUserBookingCache();
            $this->updateCache(false);
        } catch (\Exception $e) {
            throw new MicrosoftGraphCommunicationException($e->getMessage(), (int) $e->getCode());
        }

        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::COMPLETE);
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     *
     * @throws MicrosoftGraphCommunicationException
     */
    public function updateCache(bool $removeOutdated = true): void
    {
        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::INVOKE);

        try {
            $token = $this->graphHelperService->authenticateAsServiceAccount();
            $result = $this->microsoftGraphBookingService->getAllFutureBookings($token);
            $exchangeBookings = [];

            // Loop over all pages of request.
            while (isset($result['next_link'])) {
                // Loop over all elements on page.
                foreach ($result['data'] as $userBooking) {
                    // Set resource display name.
                    /** @var resource $resource */
                    $resource = $this->resourceRepository->findOneBy(['resourceMail' => $userBooking->resourceMail]);
                    if (null !== $resource) {
                        $userBooking->displayName = $resource->getResourceDisplayName() ?? $userBooking->displayName;
                    }

                    $exchangeBookings[] = $userBooking->id;
                    try {
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

                $result = $this->microsoftGraphBookingService->getAllFutureBookings($token, $result['next_link']);
            }

            if ($removeOutdated) {
                $this->removeOutdatedEntries($exchangeBookings);
            }

            $this->entityManager->flush();
        } catch (\Exception $e) {
            throw new MicrosoftGraphCommunicationException($e->getMessage(), (int) $e->getCode());
        }

        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::COMPLETE);
    }

    /**
     * {@inheritdoc}
     */
    public function addCacheEntry(UserBooking $userBooking): void
    {
        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::INVOKE);

        $entity = new UserBookingCacheEntry();
        $this->entityManager->persist($this->setCacheEntityValues($entity, $userBooking));
        $this->entityManager->flush();

        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::COMPLETE);
    }

    /**
     * {@inheritdoc}
     */
    public function addCacheEntryFromArray(array $data): void
    {
        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::INVOKE);

        $entity = new UserBookingCacheEntry();
        $this->entityManager->persist($this->setCacheEntityValuesFromArray($entity, $data));
        $this->entityManager->flush();

        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::COMPLETE);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function changeCacheEntry(string $exchangeId, array $changes): void
    {
        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::INVOKE);

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
                case 'resourceMail':
                    $entity->setResourceMail($value);
                    break;
                case 'resourceDisplayName':
                    $entity->setResourceDisplayName($value);
                    break;
            }
        }

        $this->entityManager->flush();

        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::COMPLETE);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteCacheEntry(string $exchangeId): void
    {
        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::INVOKE);

        $entity = $this->entityManager->getRepository(UserBookingCacheEntry::class)
            ->findOneBy(['exchangeId' => $exchangeId]);

        if ($entity) {
            $this->entityManager->remove($entity);
            $this->entityManager->flush();
        }

        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::COMPLETE);
    }

    /**
     * Set values for cache entity.
     *
     * @param $entity
     * @param UserBooking $userBooking
     *
     * @return UserBookingCacheEntry
     */
    private function setCacheEntityValues(UserBookingCacheEntry $entity, UserBooking $userBooking): UserBookingCacheEntry
    {
        $entity->setTitle($userBooking->subject);
        $entity->setExchangeId($userBooking->id);
        $entity->setUid($this->retrieveUidFromBody($userBooking->body) ?? '');
        $entity->setStart($userBooking->start);
        $entity->setEnd($userBooking->end);
        $entity->setStatus($userBooking->status);
        $entity->setResourceMail($userBooking->resourceMail);
        $entity->setResourceDisplayName($userBooking->displayName);
        $entity->setICalUId($userBooking->iCalUId);

        return $entity;
    }

    /**
     * Set values for cache entity.
     *
     * @param $entity
     * @param array $data
     *
     * @return UserBookingCacheEntry
     */
    private function setCacheEntityValuesFromArray(UserBookingCacheEntry $entity, array $data): UserBookingCacheEntry
    {
        $entity->setTitle($data['subject']);
        $entity->setExchangeId($data['id']);
        $entity->setUid($this->retrieveUidFromBody($data['body']) ?? '');
        $entity->setStart($data['start']);
        $entity->setEnd($data['end']);
        $entity->setStatus($data['status']);
        $entity->setResourceMail($data['resourceMail']);
        $entity->setResourceDisplayName($data['resourceDisplayName']);
        $entity->setICalUId($data['iCalUId'] ?? '');

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
        if (empty($body)) {
            return null;
        }
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
     * @param string|null $documentBodyUid
     *   The uid found in mail body
     *
     * @return string|null
     */
    private function extractRealUid(?string $documentBodyUid): ?string
    {
        return $documentBodyUid ? preg_replace('/^UID-|-UID$/', '', $documentBodyUid) : null;
    }

    /**
     * Remove outdated entries in cache.
     *
     * @param array $exchangeBookings
     *   A list of exchange IDs
     */
    private function removeOutdatedEntries(array $exchangeBookings): void
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

    /**
     * {@inheritdoc}
     */
    public function deleteCacheEntryByICalUId(string $iCalUId): void
    {
        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::INVOKE);

        $entity = $this->entityManager->getRepository(UserBookingCacheEntry::class)
            ->findOneBy(['iCalUId' => $iCalUId]);

        if ($entity) {
            $this->entityManager->remove($entity);
            $this->entityManager->flush();
        }

        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::COMPLETE);
    }
}
