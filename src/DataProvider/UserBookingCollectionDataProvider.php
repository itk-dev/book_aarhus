<?php

namespace App\DataProvider;

use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use ApiPlatform\State\Pagination\TraversablePaginator;
use App\Entity\Main\UserBooking;
use App\Entity\Resources\AAKResource;
use App\Repository\Resources\AAKResourceRepository;
use App\Security\Voter\UserBookingVoter;
use App\Service\BookingServiceInterface;
use App\Service\Metric;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class UserBookingCollectionDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{
    public function __construct(
        private readonly BookingServiceInterface $bookingService,
        private readonly Security $security,
        private readonly RequestStack $requestStack,
        private readonly AAKResourceRepository $resourceRepository,
        private readonly Metric $metric,
    ) {
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return UserBooking::class === $resourceClass;
    }

    /**
     * @throws \Exception
     */
    public function getCollection(string $resourceClass, string $operationName = null, array $context = []): iterable
    {
        $this->metric->incFunctionTotal($this, __FUNCTION__, Metric::INVOKE);

        $request = $this->requestStack->getCurrentRequest();

        if (is_null($request)) {
            throw new BadRequestHttpException('Request not set.');
        }

        $userId = $request->headers->get('Authorization-UserId') ?? null;

        if (is_null($userId)) {
            throw new BadRequestHttpException('Required Authorization-UserId header is not set.');
        }

        $page = intval($request->query->get('page'));

        $pageSize = intval($request->query->get('pageSize'));
        if (0 === $pageSize) {
            $pageSize = 25;
        }

        $search = $request->query->get('search');
        if (empty($search)) {
            $search = null;
        }

        $responseData = $this->bookingService->getUserBookings($userId, $search, $page, $pageSize);

        $userBookings = [];

        /** @var UserBooking $userBooking */
        foreach ($responseData['userBookings'] as $userBooking) {
            // Set resource display name if set in the AAKResource.
            /** @var AAKResource $resource */
            $resource = $this->resourceRepository->findOneBy(['resourceMail' => $userBooking->resourceMail]);
            if (null !== $resource) {
                $userBooking->displayName = $resource->getResourceDisplayName() ?? $userBooking->displayName;
            }

            if ($this->security->isGranted(UserBookingVoter::VIEW, $userBooking)) {
                $userBookings[] = $userBooking;
            }
        }

        $obj = new \ArrayObject($userBookings);
        $it = $obj->getIterator();

        $this->metric->incFunctionTotal($this, __FUNCTION__, Metric::COMPLETE);

        return new TraversablePaginator($it, $page, $responseData['pageSize'], $responseData['total']);
    }
}
