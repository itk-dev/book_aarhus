<?php

namespace App\Service;

interface NotificationServiceInterface
{
    public function sendBookingNotification($booking, $resource, string $type);

    public function createCalendarComponent(array $events);
}
