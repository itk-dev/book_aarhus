<?php

namespace App\Message;

use App\Entity\Api\UserBooking;
use App\Enum\NotificationTypeEnum;

class SendUserBookingNotificationMessage
{
    public function __construct(private readonly UserBooking $userBooking, private readonly NotificationTypeEnum $type)
    {
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
