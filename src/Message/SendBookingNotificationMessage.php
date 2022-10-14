<?php

namespace App\Message;

use App\Entity\Main\Booking;
use App\Service\NotificationTypeEnum;

class SendBookingNotificationMessage
{
    private Booking $booking;
    private NotificationTypeEnum $type;

    public function __construct(Booking $booking, NotificationTypeEnum $type)
    {
        $this->booking = $booking;
        $this->type = $type;
    }

    public function getBooking(): Booking
    {
        return $this->booking;
    }

    public function getType(): NotificationTypeEnum
    {
        return $this->type;
    }
}
