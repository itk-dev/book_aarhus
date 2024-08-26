<?php

namespace App\MessageHandler;

use App\Entity\Resources\AAKResource;
use App\Exception\NoNotificationReceiverException;
use App\Exception\UnsupportedNotificationTypeException;
use App\Message\SendBookingNotificationMessage;
use App\Repository\Resources\AAKResourceRepository;
use App\Service\Metric;
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
        private readonly NotificationServiceInterface $notificationService,
        private readonly Metric $metric,
    ) {}

    /**
     * @throws TransportExceptionInterface
     */
    public function __invoke(SendBookingNotificationMessage $message): void
    {
        $this->metric->counter('invoke', null, $this);

        try {
            $this->logger->info('SendBookingNotificationHandler invoked.');

            $booking = $message->getBooking();
            $type = $message->getType();

            /** @var AAKResource $resource */
            $email = $booking->getResourceEmail();
            $resource = $this->aakResourceRepository->findOneByEmail($email);

            $this->notificationService->sendBookingNotification($booking, $resource, $type);
        } catch (NoNotificationReceiverException|UnsupportedNotificationTypeException $e) {
            $this->metric->counter('generalUnrecoverableMessageHandlingException');
            $this->metric->counter('TransportExceptionInterface', null, $this);
            $this->logger->error(sprintf('SendBookingNotificationHandler exception: %d %s', $e->getCode(), $e->getMessage()));

            throw new UnrecoverableMessageHandlingException($e->getMessage(), $e->getCode(), $e);
        } catch (TransportExceptionInterface $e) {
            $this->metric->counter('TransportExceptionInterface');
            $this->metric->counter('TransportExceptionInterface', null, $this);
            $this->logger->error(sprintf('SendBookingNotificationHandler exception: %d %s', $e->getCode(), $e->getMessage()));

            throw $e;
        }
    }
}
