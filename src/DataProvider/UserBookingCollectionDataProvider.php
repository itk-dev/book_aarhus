<?php

namespace App\DataProvider;

use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Entity\Main\UserBooking;
use App\Security\Voter\UserBookingVoter;
use App\Service\MicrosoftGraphServiceInterface;
use Exception;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Security;

final class UserBookingCollectionDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{
    public function __construct(
        private readonly MicrosoftGraphServiceInterface $microsoftGraphService,
        private readonly Security $security,
        private readonly RequestStack $requestStack,
    ) {
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return UserBooking::class === $resourceClass;
    }

    /**
     * @throws Exception
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

        $userBookingData = $this->microsoftGraphService->getUserBookings($userId);

        $userBookingsHits = $userBookingData['value'][0]['hitsContainers'][0]['hits'] ?? [];

        foreach ($userBookingsHits as $hit) {
            $id = urlencode($hit['hitId']);

            $userBookingGraphData = $this->microsoftGraphService->getBooking($id);

            $userBooking = $this->microsoftGraphService->getUserBookingFromGraphData($userBookingGraphData);

            if ($this->security->isGranted(UserBookingVoter::VIEW, $userBooking)) {
                yield $userBooking;
            }
        }
    }
}
