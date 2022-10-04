<?php

// api/src/DataProvider/BlogPostItemDataProvider.php

namespace App\DataProvider;

use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Entity\Main\UserBooking;
use App\Security\Voter\UserBookingVoter;
use App\Service\MicrosoftGraphServiceInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Uid\Ulid;

final class UserBookingItemDataProvider implements ItemDataProviderInterface, RestrictedDataProviderInterface
{
    public function __construct(private MicrosoftGraphServiceInterface $microsoftGraphService, private Security $security, private RequestStack $requestStack)
    {
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return UserBooking::class === $resourceClass;
    }

    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = []): ?UserBooking
    {
        switch ($context['item_operation_name']) {
            case 'get':
                if (!isset($id)) {
                    throw new BadRequestHttpException('Required booking id is not set');
                }

                $userId = ' ';
                $userBookingResults = $this->microsoftGraphService->getUserBooking($userId, $id);
                $userBooking = new UserBooking();

                $userBooking->id = Ulid::generate();
                $userBooking->hitId = $userBookingResults['id'] ?? '';
                $userBooking->summary = $userBookingResults['summary'] ?? '';
                $userBooking->start = new \DateTime($userBookingResults['start']['dateTime'], new \DateTimeZone($userBookingResults['start']['timeZone'])) ?? null;
                $userBooking->end = new \DateTime($userBookingResults['end']['dateTime'], new \DateTimeZone($userBookingResults['end']['timeZone'])) ?? null;
                $userBooking->iCalUId = $userBookingResults['iCalUId'];
                $userBooking->subject = $userBookingResults['resource']['subject'] ?? '';

                $bookingDetailsData = $this->microsoftGraphService->getBookingDetails($userBookingResults['id']);

                $userBooking->displayName = $bookingDetailsData['location']['displayName'];
                $userBooking->body = $bookingDetailsData['body']['content'];

                if (!$this->security->isGranted(UserBookingVoter::VIEW, $userBooking)) {
                    throw new AccessDeniedHttpException('Access denied');
                }

                // TODO: Should this be exposed?
                unset($userBooking->body);

                return $userBooking;
            case 'delete':
                // TODO: Refactor to move into DataPersister instead of being in DataProvider.

                if (!isset($id)) {
                    throw new BadRequestHttpException('Required booking id is not set');
                }

                $request = $this->requestStack->getCurrentRequest();
                $userId = $request->headers->get('Authentication-UserId');

                $userBookingResults = $this->microsoftGraphService->getUserBooking($userId, $id);

                $userBooking = new UserBooking();
                $userBooking->id = Ulid::generate();
                $userBooking->hitId = $userBookingResults['id'] ?? '';
                $bookingDetailsData = $this->microsoftGraphService->getBookingDetails($userBookingResults['id']);

                $userBooking->displayName = $bookingDetailsData['location']['displayName'];
                $userBooking->body = $bookingDetailsData['body']['content'];

                if (!$this->security->isGranted(UserBookingVoter::DELETE, $userBooking)) {
                    throw new AccessDeniedHttpException('Access denied');
                }

                $this->microsoftGraphService->deleteUserBooking($id, $userId);

                return null;
        }

        return null;
    }
}
