<?php

namespace App\EventListener;

use App\Enum\NotificationTypeEnum;
use App\Interface\NotificationServiceInterface;
use App\Message\CreateBookingMessage;
use App\Message\SendBookingNotificationMessage;
use App\Message\WebformSubmitMessage;
use App\Repository\ResourceRepository;
use App\Service\MetricsHelper;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;

// We need to fire after SendFailedMessageToFailureTransportListener which has priority -100.
#[AsEventListener(priority: -101)]
final readonly class FailedMessageEventListener
{
    public function __construct(
        private ResourceRepository $resourceRepository,
        private NotificationServiceInterface $notificationService,
        private MetricsHelper $metricsHelper,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(WorkerMessageFailedEvent $event): void
    {
        try {
            $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::INVOKE);

            $envelope = $event->getEnvelope();
            $message = $envelope->getMessage();

            if ($message instanceof WebformSubmitMessage) {
                $webformId = $message->getWebformId();
                $throwable = $event->getThrowable();

                $message = sprintf('Failed to extract data (Error: %d) from webform with id: %s.', $throwable->getCode(), $webformId);

                $this->notificationService->notifyAdmin(
                    'Webform data retrieval failed',
                    $message,
                    null,
                    null
                );
            } elseif ($message instanceof CreateBookingMessage) {
                $booking = $message->getBooking();
                $resource = $this->resourceRepository->findOneByEmail($booking->getResourceEmail());

                $this->notificationService->sendBookingNotification($booking, $resource, NotificationTypeEnum::FAILED);

                $this->notificationService->notifyAdmin(
                    'Create booking failed.',
                    'Failed to create the booking in Exchange.',
                    $booking,
                    $resource
                );
            } elseif ($message instanceof SendBookingNotificationMessage) {
                $booking = $message->getBooking();
                $resource = $this->resourceRepository->findOneByEmail($booking->getResourceEmail());

                $this->notificationService->notifyAdmin(
                    'Booking notification to user failed.',
                    'Failed to send notification message to user.',
                    $booking,
                    $resource
                );
            }

            $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::COMPLETE);
        } catch (\Throwable $e) {
            try {
                $message = $e->getMessage();
                $this->logger->error($message);
            } catch (\Throwable $e) {
                // Ignore every exception to avoid blocking message handling.
            }
        }
    }
}
