<?php

namespace App\Model;

use App\Entity\Main\Resource;
use App\Entity\Main\Booking;
use App\Enum\CreateBookingStatusEnum;

class BookingRequest
{
    public ?Booking $booking = null;
    public ?Resource $resource = null;
    public ?array $createdBooking = null;

    public function __construct(public array $input, public CreateBookingStatusEnum $status)
    {
    }
}
