<?php

namespace App\Service;

use App\Entity\Main\UserBooking;
use App\Exception\BookingCreateConflictException;
use App\Exception\MicrosoftGraphCommunicationException;
use App\Exception\UserBookingException;
use DateTime;

interface BookingServiceInterface
{
    /**
     * Get a booking.
     *
     * @param string $bookingId
     *
     * @return array
     *
     * @throws MicrosoftGraphCommunicationException
     */
    public function getBooking(string $bookingId): array;

    /**
     * Update a booking.
     *
     * @param UserBooking $booking the booking with updated fields
     *
     * @return string|null
     *
     * @throws MicrosoftGraphCommunicationException
     * @throws UserBookingException
     */
    public function updateBooking(UserBooking $booking): ?string;

    /**
     * Delete a booking.
     *
     * @param UserBooking $booking
     *
     * @throws MicrosoftGraphCommunicationException
     * @throws UserBookingException
     */
    public function deleteBooking(UserBooking $booking);

    /**
     * Get bookings containing userId.
     *
     * @param string $userId
     *
     * @return array array of search hits
     *
     * @throws MicrosoftGraphCommunicationException
     */
    public function getUserBookings(string $userId): array;

    /**
     * Get busy intervals for a given number of schedules.
     *
     * @param array $schedules
     * @param DateTime $startTime
     * @param DateTime $endTime
     * @param string|null $accessToken
     *
     * @return array
     *
     * @throws MicrosoftGraphCommunicationException
     */
    public function getBusyIntervals(array $schedules, DateTime $startTime, DateTime $endTime, string $accessToken = null): array;

    /**
     * @param string $resourceEmail
     * @param string $resourceName
     * @param string $subject
     * @param string $body
     * @param DateTime $startTime
     * @param DateTime $endTime
     *
     * @return array
     *
     * @throws BookingCreateConflictException
     * @throws MicrosoftGraphCommunicationException
     */
    public function createBookingForResource(string $resourceEmail, string $resourceName, string $subject, string $body, DateTime $startTime, DateTime $endTime, bool $acceptConflict = false): array;

    /**
     * @param string $resourceEmail
     * @param string $resourceName
     * @param string $subject
     * @param string $body
     * @param DateTime $startTime
     * @param DateTime $endTime
     *
     * @return array
     *
     * @throws MicrosoftGraphCommunicationException
     */
    public function createBookingInviteResource(string $resourceEmail, string $resourceName, string $subject, string $body, DateTime $startTime, DateTime $endTime): array;

    /**
     * Create a UserBooking from graph data.
     *
     * @param array $data
     *
     * @return UserBooking
     *
     * @throws UserBookingException
     */
    public function getUserBookingFromApiData(array $data): UserBooking;

    /**
     * Create user id string for booking body.
     *
     * @param string $id user id
     *
     * @return string
     */
    public function createBodyUserId(string $id): string;
}
