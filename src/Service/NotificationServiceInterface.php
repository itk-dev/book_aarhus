<?php

namespace App\Service;

interface NotificationServiceInterface
{
    public const BOOKING_TYPE_SUCCESS = 'booking_success';
    public const BOOKING_TYPE_FAILED = 'booking_failed';
    public const BOOKING_TYPE_CHANGED = 'booking_changed';

    public function sendBookingNotification($booking, $resource, string $type);

    public function createCalendarComponent(array $events);
}
