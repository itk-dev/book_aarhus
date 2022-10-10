<?php

namespace App\MessageHandler;

use App\Entity\Resources\AAKResource;
use App\Message\SendBookingNotificationMessage;
use App\Repository\Main\AAKResourceRepository;
use App\Service\NotificationServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SendBookingNotificationHandler
{
    public function __construct(private LoggerInterface $logger, private AAKResourceRepository $aakResourceRepository, private NotificationServiceInterface $notificationService)
    {
    }

    public function __invoke(SendBookingNotificationMessage $message)
    {
        $this->logger->info('SendBookingNotificationHandler invoked.');

        $booking = $message->getBooking();
        $type = $message->getType();

        /** @var AAKResource $resource */
        $email = $booking->getResourceEmail();
        $resource = $this->aakResourceRepository->findOneByEmail($email);

        $this->notificationService->sendBookingNotification($booking, $resource, $type);
    }
}