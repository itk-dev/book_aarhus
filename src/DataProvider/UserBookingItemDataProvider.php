<?php
// api/src/DataProvider/BlogPostItemDataProvider.php

namespace App\DataProvider;

use Exception;
use Symfony\Component\Uid\Ulid;
use App\Entity\Main\UserBooking;
use App\Service\MicrosoftGraphServiceInterface;
use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class UserBookingItemDataProvider implements ItemDataProviderInterface, RestrictedDataProviderInterface
{
    public function __construct(private MicrosoftGraphServiceInterface $microsoftGraphService)
    {
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return UserBooking::class === $resourceClass;
    }

    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = []): ?UserBooking
    {
        switch ($context['item_operation_name']) {
            case "get":
                if (!isset($id)) {
                    throw new BadRequestHttpException('Required booking id is not set');
                }

                $userId = " ";
                $userBookingResults = [$this->microsoftGraphService->getUserBooking($userId, $id)];
                $userBooking = new UserBooking();

                foreach ($userBookingResults as $userBookingResult) {
                    $userBooking->id = Ulid::generate();
                    $userBooking->hitId = $userBookingResult['id'] ?? '';
                    $userBooking->summary = $userBookingResult['summary'] ?? '';
                    $userBooking->subject = $userBookingResult['resource']['subject'] ?? '';
                    $userBooking->start = new \DateTime($userBookingResult['start']['dateTime'], new \DateTimeZone($userBookingResult['start']['timeZone'])) ?? null;
                    $userBooking->end = new \DateTime($userBookingResult['end']['dateTime'], new \DateTimeZone($userBookingResult['end']['timeZone'])) ?? null;
                    $userBooking->iCalUId = $userBookingResult['iCalUId'];

                    $bookingDetailsData = [$this->microsoftGraphService->getBookingDetails($userBookingResult['id'])];

                    foreach ($bookingDetailsData as $bookingDetail) {
                        $userBooking->displayName = $bookingDetail['location']['displayName'];
                        $userBooking->body = $bookingDetail['body']['content'];
                        continue;
                    }
                }

                return $userBooking;
            case "delete":
                if (!isset($id)) {
                    throw new BadRequestHttpException('Required booking id is not set');
                }

                $userId = " ";
                try {
                    $userBooking = new UserBooking();
                    $userBookingResult = $this->microsoftGraphService->deleteUserBooking($id, $userId);
                    $userBooking->status = $userBookingResult;
                } catch(Exception $e) {
                    die($e->getMessage());
                }
                
                return $userBooking;
            default:
                return false;
        }
    }
}
