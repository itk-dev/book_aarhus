<?php

namespace App\Interface;

use App\Entity\Api\UserBooking;
use App\Exception\BookingCreateConflictException;
use App\Exception\MicrosoftGraphCommunicationException;
use App\Exception\UserBookingException;

interface BookingServiceInterface
{
    /**
     * Get a booking.
     *
     * @throws MicrosoftGraphCommunicationException
     */
    public function getBooking(string $bookingId): array;

    /**
     * Update a booking.
     *
     * @param UserBooking $booking the booking with updated fields
     *
     * @throws MicrosoftGraphCommunicationException
     * @throws UserBookingException
     */
    public function updateBooking(UserBooking $booking): ?string;

    /**
     * Delete a booking.
     *
     * @throws MicrosoftGraphCommunicationException
     * @throws UserBookingException
     */
    public function deleteBooking(UserBooking $booking);

    /**
     * Get bookings containing userId.
     *
     * @return array array of search hits
     *
     * @throws MicrosoftGraphCommunicationException
     */
    public function getUserBookings(string $userId, ?string $search = null, int $page = 0, int $pageSize = 25): array;

    /**
     * Get busy intervals for a given number of schedules.
     *
     * @throws MicrosoftGraphCommunicationException
     */
    public function getBusyIntervals(array $schedules, \DateTime $startTime, \DateTime $endTime, ?string $accessToken = null): array;

    /**
     * @throws BookingCreateConflictException
     * @throws MicrosoftGraphCommunicationException
     */
    public function createBookingForResource(string $resourceEmail, string $resourceName, string $subject, string $body, \DateTime $startTime, \DateTime $endTime, bool $acceptConflict = false): array;

    /**
     * @throws MicrosoftGraphCommunicationException
     */
    public function createBookingInviteResource(string $resourceEmail, string $resourceName, string $subject, string $body, \DateTime $startTime, \DateTime $endTime): array;

    /**
     * Create a UserBooking from graph data.
     *
     * @throws UserBookingException
     */
    public function getUserBookingFromApiData(array $data): UserBooking;

    /**
     * Create user id string for booking body.
     *
     * @param string $id user id
     */
    public function createBodyUserId(string $id): string;

    public function getAllFutureBookings(string $token, ?string $request): array;

    /**
     * Get exchange id from iCalUId.
     *
     * @param string $iCalUId the iCalUId to search for
     *
     * @return ?string the exchange id
     */
    public function getBookingIdFromICalUid(string $iCalUId): ?string;

    /**
     * Deletes booking by iCalUid.
     *
     * @throws UserBookingException
     * @throws MicrosoftGraphCommunicationException
     */
    public function deleteBookingByICalUid(string $iCalUId): void;
}
