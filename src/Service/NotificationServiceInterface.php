<?php

namespace App\Service;

interface NotificationServiceInterface
{
    public function sendBookingNotification($booking, $resource, NotificationTypeEnum $type);

    public function createCalendarComponent(array $events);
}
