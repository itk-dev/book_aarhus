<?php

namespace App\DataProvider;

use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Entity\Main\BookingDetails;
use App\Service\MicrosoftGraphServiceInterface;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Microsoft\Graph\Exception\GraphException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Uid\Ulid;

final class BookingDetailsCollectionDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{
    public function __construct(private MicrosoftGraphServiceInterface $microsoftGraphService)
    {
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return BookingDetails::class === $resourceClass;
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

        $bookingDetailsData = [$this->microsoftGraphService->getBookingDetails($bookingId)];

        foreach ($bookingDetailsData as $bookingDetail) {
            $bookingDetails = new BookingDetails();
            $bookingDetails->id = Ulid::generate();
            $bookingDetails->displayName = $bookingDetail['location']['displayName'];
            $bookingDetails->body = $bookingDetail['body']['content'];
            yield $bookingDetails;
        }
    }
}
