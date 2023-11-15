<?php

namespace App\Message;

use App\Entity\Main\Booking;

class AddBookingToCacheMessage
{
    public function __construct(private readonly Booking $booking, private readonly string $iCalUID)
    {
    }

    public function getBooking(): Booking
    {
        return $this->booking;
    }

    public function getICalUID(): string
    {
        return $this->iCalUID;
    }
}
