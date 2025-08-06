<?php

namespace App\Interface;

use App\Entity\Api\Booking;
use App\Entity\Api\UserBooking;
use App\Entity\Main\Resource;
use App\Enum\NotificationTypeEnum;
use App\Exception\NoNotificationReceiverException;
use App\Exception\UnsupportedNotificationTypeException;
use Eluceo\iCal\Presentation\Component;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

interface NotificationServiceInterface
{
    /**
     * Send notification about a booking.
     *
     * @throws NoNotificationReceiverException
     * @throws UnsupportedNotificationTypeException
     * @throws TransportExceptionInterface
     */
    public function sendBookingNotification(Booking $booking, ?Resource $resource, NotificationTypeEnum $type): void;

    /**
     * Send notification about a user booking.
     *
     * @throws NoNotificationReceiverException
     * @throws UnsupportedNotificationTypeException
     * @throws TransportExceptionInterface
     */
    public function sendUserBookingNotification(UserBooking $userBooking, ?Resource $resource, NotificationTypeEnum $type): void;

    /**
     * Create an iCal component.
     *
     * @param array $eventData the event data
     *
     * @throws \Exception
     */
    public function createCalendarComponent(array $eventData): Component;

    /**
     * Notify the admin.
     */
    public function notifyAdmin(string $subject, string $message, ?Booking $booking, ?Resource $resource): void;
}
