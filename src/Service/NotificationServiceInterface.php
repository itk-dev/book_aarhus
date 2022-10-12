<?php

namespace App\Service;

use App\Entity\Main\Booking;
use App\Entity\Resources\AAKResource;

interface NotificationServiceInterface
{
    public function sendBookingNotification(Booking $booking, AAKResource $resource, NotificationTypeEnum $type);

    public function createCalendarComponent(array $events);

    public function notifyAdmin(string $subject, string $message, ?Booking $booking, ?AAKResource $resource);
}
