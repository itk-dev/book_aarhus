<?php

namespace App\DataProvider;

use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Entity\Main\UserBooking;
use App\Security\Voter\UserBookingVoter;
use App\Service\MicrosoftGraphServiceInterface;
use GuzzleHttp\Exception\GuzzleException;
use Microsoft\Graph\Exception\GraphException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Uid\Ulid;

final class UserBookingCollectionDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{
    public function __construct(
        private readonly MicrosoftGraphServiceInterface $microsoftGraphService,
        private readonly Security $security,
        private readonly RequestStack $requestStack
    ) {
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return UserBooking::class === $resourceClass;
    }

    /**
     * @throws GraphException
     * @throws InvalidArgumentException
     * @throws GuzzleException
     */
    public function getCollection(string $resourceClass, string $operationName = null, array $context = []): iterable
    {
        $request = $this->requestStack->getCurrentRequest();

        if (is_null($request)) {
            throw new BadRequestHttpException('Request not set.');
        }

        $userId = $request->headers->get('Authorization-UserId') ?? null;

        if (is_null($userId)) {
            throw new BadRequestHttpException('Required Authorization-UserId header is not set.');
        }

        $userBookings = $this->microsoftGraphService->getUserBookings($userId);

        $userBookingsHits = $userBookings['value'][0]['hitsContainers'][0]['hits'] ?? null;

        if (null === $userBookingsHits) {
            return;
        }

        foreach ($userBookingsHits as $hit) {
            $userBooking = new UserBooking();
            $userBooking->id = Ulid::generate();
            $userBooking->hitId = $hit['hitId'] ?? '';
            $userBooking->subject = $hit['resource']['subject'] ?? '';
            $userBooking->start = new \DateTime($hit['resource']['start']['dateTime'], new \DateTimeZone($hit['resource']['start']['timeZone'])) ?? null;
            $userBooking->end = new \DateTime($hit['resource']['end']['dateTime'], new \DateTimeZone($hit['resource']['end']['timeZone'])) ?? null;
            $userBooking->summary = $hit['summary'] ?? '';

            $bookingDetailsData = $this->microsoftGraphService->getBookingDetails($hit['hitId']);

            $userBooking->displayName = $bookingDetailsData['location']['displayName'];
            $userBooking->body = $bookingDetailsData['body']['content'];

            if ($this->security->isGranted(UserBookingVoter::VIEW, $userBooking)) {
                yield $userBooking;
            }
        }
    }
}
