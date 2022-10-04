<?php

namespace App\DataProvider;

use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Entity\Main\UserBooking;
use App\Security\Voter\UserBookingVoter;
use App\Service\MicrosoftGraphServiceInterface;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Microsoft\Graph\Exception\GraphException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Uid\Ulid;

final class UserBookingCollectionDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{
    public function __construct(private MicrosoftGraphServiceInterface $microsoftGraphService, private Security $security)
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
            $userBooking->subject = $hit['resource']['subject'] ?? '';
            $userBooking->start = new \DateTime($hit['resource']['start']['dateTime'], new \DateTimeZone($hit['resource']['start']['timeZone'])) ?? null;
            $userBooking->end = new \DateTime($hit['resource']['end']['dateTime'], new \DateTimeZone($hit['resource']['end']['timeZone'])) ?? null;
            // TODO: Is the summary needed? Atm. it is hidden.
            // $userBooking->summary = $hit['summary'] ?? '';

            $bookingDetailsData = $this->microsoftGraphService->getBookingDetails($hit['hitId']);

            $userBooking->displayName = $bookingDetailsData['location']['displayName'];
            $userBooking->body = $bookingDetailsData['body']['content'];

            if ($this->security->isGranted(UserBookingVoter::VIEW, $userBooking)) {
                // TODO: Is the body needed? Atm. it is hidden.
                // The body is needed for the security voter, but should not be delivered to the user.
                // TODO: Handle this with a DTO and data transformer.
                unset($userBooking->body);
                yield $userBooking;
            }
        }
    }
}
