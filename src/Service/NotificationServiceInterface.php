<?php

namespace App\Service;

use App\Entity\Main\Booking;
use App\Entity\Resources\AAKResource;
use App\Enum\NotificationTypeEnum;
use Eluceo\iCal\Presentation\Component;
use Exception;

interface NotificationServiceInterface
{
    /**
     * Send notification about a booking.
     *
     * @param Booking $booking the booking
     * @param AAKResource|null $resource the resource
     * @param NotificationTypeEnum $type the type of notification
     *
     * @return void
     */
    public function sendBookingNotification(Booking $booking, ?AAKResource $resource, NotificationTypeEnum $type): void;

    /**
     * Create an iCol component.
     *
     * @param array $events the events
     *
     * @return Component
     *
     * @throws Exception
     */
    public function createCalendarComponent(array $events): Component;

    /**
     * Notify the admin.
     *
     * @param string $subject subject of the notification
     * @param string $message message of the notification
     * @param Booking|null $booking booking to include in the notification, if available
     * @param AAKResource|null $resource resource to include in the notification, if available
     *
     * @return void
     */
    public function notifyAdmin(string $subject, string $message, ?Booking $booking, ?AAKResource $resource): void;
}
