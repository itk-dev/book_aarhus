<?php

namespace App\DataProvider;

use Exception;
use Symfony\Component\Uid\Ulid;
use App\Entity\Main\UserBooking;
use App\Entity\Main\BookingDetails;
use GuzzleHttp\Exception\GuzzleException;
use Microsoft\Graph\Exception\GraphException;
use App\Service\MicrosoftGraphServiceInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;

final class UserBookingCollectionDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{
    public function __construct(private MicrosoftGraphServiceInterface $microsoftGraphService)
    {
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return UserBooking::class === $resourceClass;
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

        if (!isset($filters['userId'])) {
            throw new BadRequestHttpException('Required userId filter not set.');
        }

        $userId = $filters['userId'];

        $userBookings = $this->microsoftGraphService->getUserBookings($userId);
        $userBookingsHits = $userBookings['value'][0]['hitsContainers'][0]['hits'] ?? null;
        if (null === $userBookingsHits) {
            return 'no results';
        }
        foreach ($userBookingsHits as $hit) {
            $userBooking = new UserBooking();
            $userBooking->id = Ulid::generate();
            $userBooking->hitId = $hit['hitId'] ?? '';
            $userBooking->summary = $hit['summary'] ?? '';
            $userBooking->subject = $hit['resource']['subject'] ?? '';
            $userBooking->start = new \DateTime($hit['resource']['start']['dateTime'], new \DateTimeZone($hit['resource']['start']['timeZone'])) ?? null;
            $userBooking->end = new \DateTime($hit['resource']['end']['dateTime'], new \DateTimeZone($hit['resource']['end']['timeZone'])) ?? null;

            $bookingDetailsData = [$this->microsoftGraphService->getBookingDetails($hit['hitId'])];
            
            foreach ($bookingDetailsData as $bookingDetail) {
                $userBooking->displayName = $bookingDetail['location']['displayName'];
                $userBooking->body = $bookingDetail['body']['content'];
                continue;
            }
            
            yield $userBooking;
        }
    }
}
