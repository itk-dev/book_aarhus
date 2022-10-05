<?php

namespace App\Service;

use Microsoft\Graph\Http\GraphResponse;

interface MicrosoftGraphServiceInterface
{
    public function acceptBooking(string $id): ?string;

    public function getBusyIntervals(array $schedules, \DateTime $startTime, \DateTime $endTime, string $accessToken = null): array;

    public function getUserBookings(string $userId, string $bookingId = ''): array;

    public function deleteUserBooking(string $bookingId, string $ownerEmail): ?string;

    public function getBookingDetails(string $bookingId): array;

    public function createBookingForResource(string $resourceEmail, string $resourceName, string $subject, string $body, \DateTime $startTime, \DateTime $endTime): array;

    public function createBookingInviteResource(string $resourceEmail, string $resourceName, string $subject, string $body, \DateTime $startTime, \DateTime $endTime): array;

    public function request(string $endpoint, string $accessToken, string $requestType = 'GET', array $body = null): GraphResponse;

    public function authenticateAsUser(string $username, string $password): string;

    public function authenticateAsServiceAccount(): string;
}
