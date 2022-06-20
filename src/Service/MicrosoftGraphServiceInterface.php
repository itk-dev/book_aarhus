<?php

namespace App\Service;

use Microsoft\Graph\Http\GraphResponse;

interface MicrosoftGraphServiceInterface
{
    public function getBusyIntervals(array $schedules, \DateTime $startTime, \DateTime $endTime, string $accessToken = null): array;

    public function getUserBookings(string $userId): array;

    public function createBookingForResource(string $resourceEmail, string $resourceName, string $subject, string $body, \DateTime $startTime, \DateTime $endTime): array;

    public function request(string $endpoint, string $accessToken, string $requestType = 'GET', array $body = null): GraphResponse;

    public function authenticateAsUser($username, $password): \stdClass;

    public function authenticateAsServiceAccount(): string;
}
