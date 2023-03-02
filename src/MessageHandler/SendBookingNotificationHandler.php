<?php

namespace App\MessageHandler;

use App\Entity\Resources\AAKResource;
use App\Exception\NoNotificationRecieverException;
use App\Exception\UnsupportedNotificationTypeException;
use App\Message\SendBookingNotificationMessage;
use App\Repository\Resources\AAKResourceRepository;
use App\Service\NotificationServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

#[AsMessageHandler]
class SendBookingNotificationHandler
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly AAKResourceRepository $aakResourceRepository,
        private readonly NotificationServiceInterface $notificationService)
    {
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function __invoke(SendBookingNotificationMessage $message): void
    {
        try {
            $this->logger->info('SendBookingNotificationHandler invoked.');

            $booking = $message->getBooking();
            $type = $message->getType();

            /** @var AAKResource $resource */
            $email = $booking->getResourceEmail();
            $resource = $this->aakResourceRepository->findOneByEmail($email);

            $this->notificationService->sendBookingNotification($booking, $resource, $type);
        } catch (NoNotificationRecieverException|UnsupportedNotificationTypeException $e) {
            $this->logger->error(sprintf('SendBookingNotificationHandler exception: %d %s', $e->getCode(), $e->getMessage()));

            throw new UnrecoverableMessageHandlingException($e->getMessage(), $e->getCode(), $e);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error(sprintf('SendBookingNotificationHandler exception: %d %s', $e->getCode(), $e->getMessage()));

            throw $e;
        }
    }
}
