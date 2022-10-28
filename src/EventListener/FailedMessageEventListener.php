<?php

namespace App\EventListener;

use App\Enum\NotificationTypeEnum;
use App\Message\CreateBookingMessage;
use App\Message\SendBookingNotificationMessage;
use App\Message\WebformSubmitMessage;
use App\Repository\Main\AAKResourceRepository;
use App\Service\NotificationServiceInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;

#[AsEventListener]
final class FailedMessageEventListener
{
    public function __construct(
        private readonly AAKResourceRepository $AAKResourceRepository,
        private readonly NotificationServiceInterface $notificationService,
    ) {
    }

    public function __invoke(WorkerMessageFailedEvent $event): void
    {
        $envelope = $event->getEnvelope();
        $message = $envelope->getMessage();

        if ($message instanceof WebformSubmitMessage) {
            $this->notificationService->notifyAdmin(
                'Webform data retrieval failed.',
                'Failed to extract data from webform.',
                null,
                null
            );
        } elseif ($message instanceof CreateBookingMessage) {
            $booking = $message->getBooking();
            $resource = $this->AAKResourceRepository->findOneByEmail($booking->getResourceEmail());

            $this->notificationService->sendBookingNotification($booking, $resource, NotificationTypeEnum::FAILED);

            $this->notificationService->notifyAdmin(
                'Create booking failed.',
                'Failed to create the booking in Exchange.',
                $booking,
                $resource
            );
        } elseif ($message instanceof SendBookingNotificationMessage) {
            $booking = $message->getBooking();
            $resource = $this->AAKResourceRepository->findOneByEmail($booking->getResourceEmail());

            $this->notificationService->notifyAdmin(
                'Booking notification to user failed.',
                'Failed to send notification message to user.',
                $booking,
                $resource
            );
        }
    }
}
