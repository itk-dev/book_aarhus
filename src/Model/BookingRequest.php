<?php

namespace App\Model;

use App\Entity\Main\Booking;
use App\Entity\Resources\AAKResource;
use App\Enum\CreateBookingStatusEnum;

class BookingRequest
{
    public array $input;
    public CreateBookingStatusEnum $status;
    public ?Booking $booking = null;
    public ?AAKResource $resource = null;
    public ?array $createdBooking = null;

    public function __construct(array $input, CreateBookingStatusEnum $status)
    {
        $this->input = $input;
        $this->status = $status;
    }
}
