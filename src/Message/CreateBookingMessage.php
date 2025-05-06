<?php

namespace App\Message;

use App\Entity\Api\Booking;

class CreateBookingMessage
{
    public function __construct(private readonly Booking $booking)
    {
    }

    public function getBooking(): Booking
    {
        return $this->booking;
    }
}
