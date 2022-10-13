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
     * @throws \Exception
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
            $userBooking->hitId = urlencode($hit['hitId']);

            $bookingDetailsData = $this->microsoftGraphService->getBooking($userBooking->hitId);

            $userBooking->subject = $bookingDetailsData['subject'] ?? '';
            $userBooking->start = new \DateTime($bookingDetailsData['start']['dateTime'], new \DateTimeZone($bookingDetailsData['start']['timeZone'])) ?? null;
            $userBooking->end = new \DateTime($bookingDetailsData['end']['dateTime'], new \DateTimeZone($bookingDetailsData['end']['timeZone'])) ?? null;
            $userBooking->displayName = $bookingDetailsData['location']['displayName'];
            $userBooking->body = $bookingDetailsData['body']['content'];
            $userBooking->id = urlencode($bookingDetailsData['id']);

            if ($this->security->isGranted(UserBookingVoter::VIEW, $userBooking)) {
                yield $userBooking;
            }
        }
    }
}
