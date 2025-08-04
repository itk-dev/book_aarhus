<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\TraversablePaginator;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Api\UserBooking;
use App\Entity\Main\Resource;
use App\Exception\MicrosoftGraphCommunicationException;
use App\Interface\BookingServiceInterface;
use App\Repository\ResourceRepository;
use App\Security\Voter\UserBookingVoter;
use App\Service\MetricsHelper;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @template-implements ProviderInterface<object>
 */
class UserBookingCollectionProvider implements ProviderInterface
{
    public function __construct(
        private readonly BookingServiceInterface $bookingService,
        private readonly Security $security,
        private readonly RequestStack $requestStack,
        private readonly ResourceRepository $resourceRepository,
        private readonly MetricsHelper $metricsHelper,
    ) {
    }

    public function supports(string $resourceClass): bool
    {
        return UserBooking::class === $resourceClass;
    }

    /**
     * @throws MicrosoftGraphCommunicationException
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object
    {
        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::INVOKE);

        $request = $this->requestStack->getCurrentRequest();

        if (is_null($request)) {
            $this->metricsHelper->incExceptionTotal(BadRequestHttpException::class);
            throw new BadRequestHttpException('Request not set.');
        }

        $userId = $request->headers->get('Authorization-UserId') ?? null;

        if (is_null($userId)) {
            $this->metricsHelper->incExceptionTotal(BadRequestHttpException::class);
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
            /** @var resource $resource */
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

        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::COMPLETE);

        return new TraversablePaginator($it, $page, $responseData['pageSize'], $responseData['total']);
    }
}
