<?php

namespace App\Service;

use App\Exception\BookingCreateException;
use App\Exception\MicrosoftGraphCommunicationException;
use DateTime;
use Microsoft\Graph\Http\GraphResponse;

interface MicrosoftGraphServiceInterface
{
    /**
     * Accept a booking.
     *
     * @param string $id
     *
     * @return string|null
     *
     * @throws MicrosoftGraphCommunicationException
     */
    public function acceptBooking(string $id): ?string;

    /**
     * Update a booking.
     *
     * @param string $id
     * @param array $newData
     *
     * @return string|null
     *
     * @throws MicrosoftGraphCommunicationException
     */
    public function updateBooking(string $id, array $newData): ?string;

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
     * Delete a booking.
     *
     * @param string $bookingId
     * @param string $ownerEmail
     *
     * @return string|null
     *
     * @throws MicrosoftGraphCommunicationException
     */
    public function deleteUserBooking(string $bookingId, string $ownerEmail): ?string;

    /**
     * Get a booking.
     *
     * @param string $bookingId
     *
     * @return array
     *
     * @throws MicrosoftGraphCommunicationException
     */
    public function getUserBooking(string $bookingId): array;

    /**
     * Get bookings containing userId.
     *
     * @param string $userId
     *
     * @return array
     *
     * @throws MicrosoftGraphCommunicationException
     */
    public function getUserBookings(string $userId): array;

    /**
     * @param string $bookingId
     *
     * @return array
     *
     * @throws MicrosoftGraphCommunicationException
     */
    public function getBookingDetails(string $bookingId): array;

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
