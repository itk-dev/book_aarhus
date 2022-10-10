<?php

namespace App\EventListener;

use App\Message\CreateBookingMessage;
use App\Message\WebformSubmitMessage;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;

#[AsEventListener]
final class FailedMessageEventListener
{
    public function __invoke(WorkerMessageFailedEvent $event): void
    {
        $envelope = $event->getEnvelope();
        $message = $envelope->getMessage();

        if ($message instanceof WebformSubmitMessage) {
            // TODO: How should it be handled that the message cannot be retrieved from the webform?
        } elseif ($message instanceof CreateBookingMessage) {
            $booking = $message->getBooking();

            // TODO: Get user's email and send notification to mail.

            // TODO: Save user's email in booking entity instead of only in body.


            // TODO: Send notification to service mailbox?
        }
    }
}
