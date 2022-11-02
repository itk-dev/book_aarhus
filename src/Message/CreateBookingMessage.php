<?php

namespace App\Message;

use App\Entity\Main\Booking;

class CreateBookingMessage
{
    private Booking $booking;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    public function getBooking(): Booking
    {
        return $this->booking;
    }
}
