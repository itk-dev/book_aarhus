<?php

namespace App\Service;

use Microsoft\Graph\Http\GraphResponse;

interface MicrosoftGraphServiceInterface
{
    public function getFreeBusy(array $schedules, \DateTime $startTime, \DateTime $endTime, string $accessToken = null): array;

    public function request(string $endpoint, string $accessToken, string $requestType = 'GET', array $body = null): GraphResponse;

    public function authenticateAsUser($username, $password): string;

    public function authenticateAsServiceAccount(): string;
}
