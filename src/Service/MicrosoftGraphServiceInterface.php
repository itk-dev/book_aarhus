<?php

namespace App\Service;

use App\Exception\BookingCreateException;
use App\Exception\MicrosoftGraphCommunicationException;
use DateTime;
use Microsoft\Graph\Http\GraphResponse;

interface MicrosoftGraphServiceInterface
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
     * @param string $bookingId
     * @param array $newData
     *
     * @return string|null
     *
     * @throws MicrosoftGraphCommunicationException
     */
    public function updateBooking(string $bookingId, array $newData): ?string;

    /**
     * Delete a booking.
     *
     * @param string $bookingId
     * @param string $ownerEmail
     *
     * @return string|null
     *
     * @throws MicrosoftGraphCommunicationException
     */
    public function deleteBooking(string $bookingId, string $ownerEmail): ?string;

    /**
     * Accept a booking.
     *
     * @param string $bookingId
     *
     * @return string|null
     *
     * @throws MicrosoftGraphCommunicationException
     */
    public function acceptBooking(string $bookingId): ?string;

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
     * @throws BookingCreateException
     * @throws MicrosoftGraphCommunicationException
     */
    public function createBookingForResource(string $resourceEmail, string $resourceName, string $subject, string $body, DateTime $startTime, DateTime $endTime): array;

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
     * Send a request to Microsoft Graph.
     *
     * @param string $path Request path
     * @param string $accessToken Access token
     * @param string $requestType Request type
     * @param array|null $body Optional request body
     *
     * @return GraphResponse
     *
     * @throws MicrosoftGraphCommunicationException
     */
    public function request(string $path, string $accessToken, string $requestType = 'GET', array $body = null): GraphResponse;

    /**
     * Authenticate with username/password.
     *
     * @param string $username
     * @param string $password
     *
     * @return array
     *
     * @throws MicrosoftGraphCommunicationException
     */
    public function authenticateAsUser(string $username, string $password): array;

    /**
     * Authenticate as service account.
     *
     * @return string token
     *
     * @throws MicrosoftGraphCommunicationException
     */
    public function authenticateAsServiceAccount(): string;
}
