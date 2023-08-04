<?php

namespace App\DataProvider;

use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Entity\Main\UserBooking;
use App\Entity\Resources\AAKResource;
use App\Repository\Resources\AAKResourceRepository;
use App\Security\Voter\UserBookingVoter;
use App\Service\BookingServiceInterface;
use Exception;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Security;

final class UserBookingCollectionDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{
    public function __construct(
        private readonly BookingServiceInterface $bookingService,
        private readonly Security $security,
        private readonly RequestStack $requestStack,
        private readonly AAKResourceRepository $resourceRepository,
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

        $userBookingData = $this->bookingService->getUserBookings($userId);

        /** @var UserBooking $userBooking */
        foreach ($userBookingData as $userBooking) {
            // Set resource display name if set in the AAKResource.
            /** @var AAKResource $resource */
            $resource = $this->resourceRepository->findOneBy(['resourceMail' => $userBooking->resourceMail]);
            if (null != $resource) {
                $userBooking->displayName = $resource->getResourceDisplayName() ?? $userBooking->displayName;
            }

            if ($this->security->isGranted(UserBookingVoter::VIEW, $userBooking)) {
                yield $userBooking;
            }
        }
    }
}
