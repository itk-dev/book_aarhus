<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Api\UserBooking;
use App\Exception\MicrosoftGraphCommunicationException;
use App\Exception\UserBookingException;
use App\Interface\BookingServiceInterface;
use App\Security\Voter\UserBookingVoter;
use App\Service\MetricsHelper;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @template-implements ProviderInterface<object>
 */
class UserBookingItemProvider implements ProviderInterface
{
    public function __construct(
        private readonly BookingServiceInterface $bookingService,
        private readonly Security $security,
        private readonly MetricsHelper $metricsHelper,
    ) {
    }

    public function supports(string $resourceClass): bool
    {
        return UserBooking::class === $resourceClass;
    }

    /**
     * @throws MicrosoftGraphCommunicationException
     * @throws UserBookingException
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::INVOKE);

        if (!isset($uriVariables['id']) || !is_string($uriVariables['id'])) {
            $this->metricsHelper->incExceptionTotal(BadRequestHttpException::class);
            throw new BadRequestHttpException('Required booking id is not set');
        }

        $userBookingGraphData = $this->bookingService->getBooking($uriVariables['id']);

        $userBooking = $this->bookingService->getUserBookingFromApiData($userBookingGraphData);

        if (!$this->security->isGranted(UserBookingVoter::VIEW, $userBooking)) {
            $this->metricsHelper->incExceptionTotal(AccessDeniedHttpException::class);
            throw new AccessDeniedHttpException('Access denied');
        }

        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::COMPLETE);

        return $userBooking;
    }
}
