<?php

namespace App\DataProvider;

use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Entity\Main\UserBooking;
use App\Security\Voter\UserBookingVoter;
use App\Service\MicrosoftGraphServiceInterface;
use Exception;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Security;

final class UserBookingItemDataProvider implements ItemDataProviderInterface, RestrictedDataProviderInterface
{
    public function __construct(
        private readonly MicrosoftGraphServiceInterface $microsoftGraphService,
        private readonly Security $security,
    ) {
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return UserBooking::class === $resourceClass;
    }

    /**
     * @throws Exception
     */
    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = []): UserBooking|null
    {
        if (!isset($id) || !is_string($id)) {
            throw new BadRequestHttpException('Required booking id is not set');
        }

        $userBookingGraphData = $this->microsoftGraphService->getBooking($id);

        $userBooking = $this->microsoftGraphService->getUserBookingFromGraphData($userBookingGraphData);

        if (!$this->security->isGranted(UserBookingVoter::VIEW, $userBooking)) {
            throw new AccessDeniedHttpException('Access denied');
        }

        return $userBooking;
    }
}
