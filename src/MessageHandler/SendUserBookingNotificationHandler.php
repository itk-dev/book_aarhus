<?php

namespace App\MessageHandler;

use App\Entity\Resources\AAKResource;
use App\Message\SendUserBookingNotificationMessage;
use App\Repository\Main\AAKResourceRepository;
use App\Service\NotificationServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SendUserBookingNotificationHandler
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly AAKResourceRepository $aakResourceRepository,
        private readonly NotificationServiceInterface $notificationService)
    {
    }

    public function __invoke(SendUserBookingNotificationMessage $message): void
    {
        $this->logger->info('SendBookingNotificationHandler invoked.');

        $userBooking = $message->getUserBooking();
        $type = $message->getType();

        /** @var AAKResource $resource */
        $email = $userBooking->resourceMail;
        $resource = $this->aakResourceRepository->findOneByEmail($email);

        $this->notificationService->sendUserBookingNotification($userBooking, $resource, $type);
    }
}
