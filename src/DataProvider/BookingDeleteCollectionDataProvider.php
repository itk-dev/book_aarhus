<?php

namespace App\DataProvider;

use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Entity\BookingDelete;
use App\Service\MicrosoftGraphServiceInterface;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Microsoft\Graph\Exception\GraphException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class BookingDeleteCollectionDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{
    public function __construct(private MicrosoftGraphServiceInterface $microsoftGraphService)
    {
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return BookingDelete::class === $resourceClass;
    }

    /**
     * @throws GuzzleException
     * @throws GraphException
     * @throws Exception
     */
    public function getCollection(string $resourceClass, string $operationName = null, array $context = []): iterable
    {
        if (!isset($context['filters'])) {
            throw new BadRequestHttpException('Required filters not set.');
        }

        $filters = $context['filters'];

        if (!isset($filters['bookingId'])) {
            throw new BadRequestHttpException('Required bookingId filter not set.');
        }

        $bookingId = $filters['bookingId'];
        $bookingDeletion = [$this->microsoftGraphService->deleteBooking($bookingId, '')];

        foreach ($bookingDeletion as $status) {
            $bookingDelete = new BookingDelete();
            $bookingDelete->status = 'test';

            yield $bookingDelete;
        }
    }
}
