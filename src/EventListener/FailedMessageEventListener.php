<?php

namespace App\EventListener;

use App\Message\CreateBookingMessage;
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
            // TODO: Notify an administrative mailbox of error.
        } elseif ($message instanceof CreateBookingMessage) {
            $booking = $message->getBooking();
            $resource = $this->AAKResourceRepository->findOneByEmail($booking->getResourceEmail());

            $this->notificationService->sendBookingNotification($booking, $resource, NotificationServiceInterface::BOOKING_TYPE_FAILED);

            // TODO: Notify an administrative mailbox of error.
        }
    }
}
