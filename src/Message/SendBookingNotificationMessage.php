<?php

namespace App\Message;

use App\Entity\Main\Booking;

class SendBookingNotificationMessage
{
    private Booking $booking;
    private string $type;

    public function __construct(Booking $booking, string $type)
    {
        $this->booking = $booking;
        $this->type = $type;
    }

    public function getBooking(): Booking
    {
        return $this->booking;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
