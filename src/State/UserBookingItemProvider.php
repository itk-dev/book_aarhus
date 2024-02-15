<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Main\UserBooking;
use App\Security\Voter\UserBookingVoter;
use App\Service\BookingServiceInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class UserBookingItemProvider implements ProviderInterface
{
    public function __construct(
        private readonly BookingServiceInterface $bookingService,
        private readonly Security $security,
    ) {
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return UserBooking::class === $resourceClass;
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if (!isset($id) || !is_string($id)) {
            throw new BadRequestHttpException('Required booking id is not set');
        }

        $userBookingGraphData = $this->bookingService->getBooking($id);

        $userBooking = $this->bookingService->getUserBookingFromApiData($userBookingGraphData);

        if (!$this->security->isGranted(UserBookingVoter::VIEW, $userBooking)) {
            throw new AccessDeniedHttpException('Access denied');
        }

        return $userBooking;
    }
}
