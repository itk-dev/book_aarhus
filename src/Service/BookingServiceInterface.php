<?php

namespace App\Service;

use App\Entity\Main\UserBooking;
use App\Exception\UserBookingException;

interface BookingServiceInterface
{
    /**
     * Create a UserBooking from graph data.
     *
     * @param array $data
     *
     * @return UserBooking
     *
     * @throws UserBookingException
     */
    public function getUserBookingFromGraphData(array $data): UserBooking;

    /**
     * Create booking id string for booking body.
     *
     * @return string
     */
    public function createBodyBookingId(): string;

    /**
     * Create user id string for booking body.
     *
     * @param string $id user id
     *
     * @return string
     */
    public function createBodyUserId(string $id): string;
}
