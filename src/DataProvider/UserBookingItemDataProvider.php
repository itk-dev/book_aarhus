<?php

// api/src/DataProvider/BlogPostItemDataProvider.php

namespace App\DataProvider;

use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Entity\Main\UserBooking;
use App\Service\MicrosoftGraphServiceInterface;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Microsoft\Graph\Exception\GraphException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Uid\Ulid;

final class UserBookingItemDataProvider implements ItemDataProviderInterface, RestrictedDataProviderInterface
{
    public function __construct(
        private readonly MicrosoftGraphServiceInterface $microsoftGraphService
    ) {
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return UserBooking::class === $resourceClass;
    }

    /**
     * @throws GuzzleException
     * @throws GraphException
     * @throws Exception
     */
    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = []): UserBooking|false|null
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
                $userBooking->subject = $userBookingResults['resource']['subject'] ?? '';
                $userBooking->start = new \DateTime($userBookingResults['start']['dateTime'], new \DateTimeZone($userBookingResults['start']['timeZone'])) ?? null;
                $userBooking->end = new \DateTime($userBookingResults['end']['dateTime'], new \DateTimeZone($userBookingResults['end']['timeZone'])) ?? null;
                $userBooking->iCalUId = $userBookingResults['iCalUId'];

                $bookingDetailsData = $this->microsoftGraphService->getBookingDetails($userBookingResults['id']);

                $userBooking->displayName = $bookingDetailsData['location']['displayName'];
                $userBooking->body = $bookingDetailsData['body']['content'];

                return $userBooking;
            case 'delete':
                if (!isset($id)) {
                    throw new BadRequestHttpException('Required booking id is not set');
                }

                $userId = ' ';
                try {
                    $userBooking = new UserBooking();
                    $userBookingResult = $this->microsoftGraphService->deleteUserBooking($id, $userId);
                    $userBooking->status = $userBookingResult;
                } catch (Exception $e) {
                    exit($e->getMessage());
                }

                return $userBooking;
            default:
                return false;
        }
    }
}
