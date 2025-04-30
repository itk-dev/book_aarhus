<?php

namespace App\Message;

use App\Entity\Main\Booking;
use App\Enum\NotificationTypeEnum;

class SendBookingNotificationMessage
{
    public function __construct(private readonly Booking $booking, private readonly NotificationTypeEnum $type)
    {
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
