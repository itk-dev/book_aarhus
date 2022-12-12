<?php

namespace App\Message;

use App\Entity\Main\UserBooking;
use App\Enum\NotificationTypeEnum;

class SendUserBookingNotificationMessage
{
    private UserBooking $userBooking;
    private NotificationTypeEnum $type;

    public function __construct(UserBooking $booking, NotificationTypeEnum $type)
    {
        $this->userBooking = $booking;
        $this->type = $type;
    }

    public function getUserBooking(): UserBooking
    {
        return $this->userBooking;
    }

    public function getType(): NotificationTypeEnum
    {
        return $this->type;
    }
}
