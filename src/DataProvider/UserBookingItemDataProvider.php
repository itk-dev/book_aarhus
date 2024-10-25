<?php

namespace App\DataProvider;

use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Entity\Main\UserBooking;
use App\Security\Voter\UserBookingVoter;
use App\Service\BookingServiceInterface;
use App\Service\MetricsHelper;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class UserBookingItemDataProvider implements ItemDataProviderInterface, RestrictedDataProviderInterface
{
    public function __construct(
        private readonly BookingServiceInterface $bookingService,
        private readonly Security $security,
        private readonly MetricsHelper $metricsHelper,
    ) {
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return UserBooking::class === $resourceClass;
    }

    /**
     * @throws \Exception
     */
    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = []): UserBooking|null
    {
        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::INVOKE);

        if (!isset($id) || !is_string($id)) {
            throw new BadRequestHttpException('Required booking id is not set');
        }

        $userBookingGraphData = $this->bookingService->getBooking($id);

        $userBooking = $this->bookingService->getUserBookingFromApiData($userBookingGraphData);

        if (!$this->security->isGranted(UserBookingVoter::VIEW, $userBooking)) {
            throw new AccessDeniedHttpException('Access denied');
        }

        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::COMPLETE);

        return $userBooking;
    }
}
