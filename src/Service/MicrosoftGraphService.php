<?php

namespace App\Service;

use App\Exception\BookingCreateException;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use JsonException;
use Microsoft\Graph\Exception\GraphException;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Http\GraphResponse;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * @see https://github.com/microsoftgraph/msgraph-sdk-php
 * @see https://docs.microsoft.com/en-us/graph/use-the-api
 */
class MicrosoftGraphService implements MicrosoftGraphServiceInterface
{
    // see https://docs.microsoft.com/en-us/graph/api/resources/datetimetimezone?view=graph-rest-1.0
    // example 2019-03-15T09:00:00
    public const DATE_FORMAT = 'Y-m-d\TH:i:s';

    public function __construct(
        private readonly string $tenantId,
        private readonly string $clientId,
        private readonly string $serviceAccountUsername,
        private readonly string $serviceAccountPassword,
        private readonly string $serviceAccountName,
        private readonly CacheInterface $cache
    ) {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function authenticateAsServiceAccount(): string
    {
        return $this->cache->get('serviceAccountToken', function (CacheItemInterface $item) {
            $tokenEntry = $this->authenticateAsUser($this->serviceAccountUsername, $this->serviceAccountPassword);

            $item->expiresAfter($tokenEntry['expires_in'] ?? 3600);

            return $tokenEntry['access_token'];
        });
    }

    /**
     * @throws GuzzleException
     * @throws JsonException
     */
    public function authenticateAsUser(string $username, string $password): array
    {
        $guzzle = new Client();
        $url = 'https://login.microsoftonline.com/'.$this->tenantId.'/oauth2/v2.0/token';

        $response = $guzzle->post($url, [
            'form_params' => [
                'client_id' => $this->clientId,
                'scope' => 'https://graph.microsoft.com/.default',
                'username' => $username,
                'password' => $password,
                'grant_type' => 'password',
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @throws GraphException
     * @throws GuzzleException
     */
    public function request(string $endpoint, string $accessToken, string $requestType = 'GET', array $body = null): GraphResponse
    {
        $graph = new Graph();
        $graph->setAccessToken($accessToken);

        $graphRequest = $graph->createRequest($requestType, $endpoint);

        if ($body) {
            $graphRequest->attachBody($body);
        }

        return $graphRequest->execute();
    }

    /**
     * @see https://docs.microsoft.com/en-us/graph/api/calendar-getschedule?view=graph-rest-1.0&tabs=http
     *
     * @throws GraphException
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function getBusyIntervals(array $schedules, \DateTime $startTime, \DateTime $endTime, string $accessToken = null): array
    {
        // Use service account if accessToken is not set.
        $token = $accessToken ?: $this->authenticateAsServiceAccount();

        $body = [
            'schedules' => $schedules,
            'startTime' => [
                'dateTime' => $startTime->setTimezone(new \DateTimeZone('UTC'))->format(MicrosoftGraphService::DATE_FORMAT),
                'timeZone' => 'UTC',
            ],
            'endTime' => [
                'dateTime' => $endTime->setTimezone(new \DateTimeZone('UTC'))->format(MicrosoftGraphService::DATE_FORMAT),
                'timeZone' => 'UTC',
            ],
        ];

        $response = $this->request('/me/calendar/getSchedule', $token, 'POST', $body);

        $data = $response->getBody();

        $scheduleData = $data['value'];

        $result = [];

        foreach ($scheduleData as $schedule) {
            $scheduleResult = [];

            foreach ($schedule['scheduleItems'] as $scheduleItem) {
                $scheduleResult[] = [
                    'startTime' => $scheduleItem['start'],
                    'endTime' => $scheduleItem['end'],
                ];
            }

            $result[$schedule['scheduleId']] = $scheduleResult;
        }

        return $result;
    }

    /**
     * @see https://docs.microsoft.com/en-us/graph/api/user-post-events?view=graph-rest-1.0&tabs=http#examples
     *
     * @throws GraphException
     * @throws GuzzleException
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function createBookingForResource(string $resourceEmail, string $resourceName, string $subject, string $body, \DateTime $startTime, \DateTime $endTime): array
    {
        $token = $this->authenticateAsServiceAccount();

        // Search interval for existing bookings. Report error if interval is booked already.
        $busyIntervals = $this->getBusyIntervals([$resourceEmail], $startTime, $endTime, $token);

        if (!empty($busyIntervals[$resourceEmail])) {
            throw new BookingCreateException('Booking interval conflict.', 409);
        }

        $body = [
            'subject' => $subject,
            'body' => [
                'contentType' => 'HTML',
                'content' => $body,
            ],
            'end' => [
                'dateTime' => $endTime->setTimezone(new \DateTimeZone('UTC'))->format(MicrosoftGraphService::DATE_FORMAT),
                'timeZone' => 'UTC',
            ],
            'start' => [
                'dateTime' => $startTime->setTimezone(new \DateTimeZone('UTC'))->format(MicrosoftGraphService::DATE_FORMAT),
                'timeZone' => 'UTC',
            ],
            'allowNewTimeProposals' => false,
            'showAs' => 'busy',
            'isOrganizer' => false,
            'location' => [
                'displayName' => $resourceName,
                'locationEmailAddress' => $resourceEmail,
            ],
            'attendees' => [
                [
                    'emailAddress' => [
                        'address' => $this->serviceAccountUsername,
                        'name' => $this->serviceAccountName,
                    ],
                    'type' => 'optional',
                ],
            ],
        ];

        $response = $this->request("/users/$resourceEmail/events", $token, 'POST', $body);

        // Make sure only the new booking exists in the interval.
        $busyIntervals = $this->getBusyIntervals([$resourceEmail], $startTime, $endTime, $token);

        if (empty($busyIntervals[$resourceEmail])) {
            throw new BookingCreateException('Booking was not created.', 404);
        }

        // TODO: Decide if this should be added.
        /*
        if (count($busyIntervals[$resourceEmail]) > 1) {
            // TODO: Remove booking again.
            throw new \Exception('Booking interval conflict.', 409);
        }
        */

        return $response->getBody();
    }

    /**
     * @see https://docs.microsoft.com/en-us/graph/api/user-post-events?view=graph-rest-1.0&tabs=http#examples
     *
     * @throws GraphException
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function createBookingInviteResource(string $resourceEmail, string $resourceName, string $subject, string $body, \DateTime $startTime, \DateTime $endTime): array
    {
        $token = $this->authenticateAsServiceAccount();

        $body = [
            'subject' => $subject,
            'body' => [
                'contentType' => 'HTML',
                'content' => $body,
            ],
            'end' => [
                'dateTime' => $endTime->setTimezone(new \DateTimeZone('UTC'))->format(MicrosoftGraphService::DATE_FORMAT),
                'timeZone' => 'UTC',
            ],
            'start' => [
                'dateTime' => $startTime->setTimezone(new \DateTimeZone('UTC'))->format(MicrosoftGraphService::DATE_FORMAT),
                'timeZone' => 'UTC',
            ],
            'allowNewTimeProposals' => false,
            'showAs' => 'busy',
            'location' => [
                'displayName' => $resourceName,
                'locationEmailAddress' => $resourceEmail,
            ],
            'attendees' => [
                [
                    'emailAddress' => [
                        'address' => $resourceEmail,
                        'name' => $resourceName,
                    ],
                    'type' => 'resource',
                ],
            ],
        ];

        $response = $this->request('/me/events', $token, 'POST', $body);

        return $response->getBody();
    }

    /**
     * @throws GraphException
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function acceptBooking(string $id): ?string
    {
        $token = $this->authenticateAsServiceAccount();

        $urlEncodedId = urlencode($id);

        $response = $this->request("/me/events/$urlEncodedId/accept", $token, 'POST', [
            'sendResponse' => false,
        ]);

        return $response->getStatus();
    }

    /**
     * @see https://docs.microsoft.com/en-us/graph/api/event-update?view=graph-rest-1.0&tabs=http
     *
     * @throws GraphException
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function updateBooking(string $id, array $newData = []): ?string
    {
        $token = $this->authenticateAsServiceAccount();

        $urlEncodedId = urlencode($id);

        $response = $this->request("/me/events/$urlEncodedId", $token, 'PATCH', $newData);

        return $response->getStatus();
    }

    /**
     * @see https://docs.microsoft.com/en-us/graph/api/event-delete?view=graph-rest-1.0&tabs=http
     *
     * @throws GraphException
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function deleteUserBooking(string $bookingId, string $ownerEmail): ?string
    {
        $token = $this->authenticateAsServiceAccount();

        // Formatting the urldecode(d) booking hitId, replacing "/" with "-" as this is graph-compatible, and replacing
        // " " with "+", as some encoding issue between javascript and php replaces "+" with " ".
        $bookingId = urldecode($bookingId);
        $bookingId = str_replace(['/', ' '], ['-', '+'], $bookingId);

        // TODO: Handle request for deletion of resource-event
        // We need the HitId from the resource-event, to request the deletion of that event.
        // $urlEncodedId = urlencode($id);
        // $encodedOwnerEmail = urlencode($ownerEmail);
        // $response = $this->request("/users/$encodedOwnerEmail/events/$urlEncodedId", $token, 'DELETE');

        $response = $this->request("/me/events/$bookingId", $token, 'DELETE');

        return $response->getStatus();
    }

    /**
     * @see https://docs.microsoft.com/en-us/graph/search-concept-events
     *
     * @throws GraphException
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function getUserBooking(string $bookingId): array
    {
        $token = $this->authenticateAsServiceAccount();

        $bookingId = urldecode($bookingId);
        $bookingId = str_replace(['/', ' '], ['-', '+'], $bookingId);

        $response = $this->request('/me/events/'.$bookingId, $token, 'GET', null);

        return $response->getBody();
    }

    /**
     * @see https://docs.microsoft.com/en-us/graph/search-concept-events
     *
     * @throws GraphException
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function getUserBookings(string $userId, string $bookingId = ''): array
    {
        $token = $this->authenticateAsServiceAccount();

        $body = [
            'requests' => [
                [
                    'entityTypes' => ['event'],
                    'query' => [
                        'queryString' => "[userid-$userId]",
                    ],
                    'from' => 0,
                    'to' => 100,
                ],
            ],
        ];

        $response = $this->request('/search/query', $token, 'POST', $body);

        return $response->getBody();
    }

    /**
     * @see https://docs.microsoft.com/en-us/graph/search-concept-events
     *
     * @throws GraphException
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function getBookingDetails(string $bookingId): array
    {
        $token = $this->authenticateAsServiceAccount();

        $bookingId_formatted = str_replace(['/', ' '], ['-', '+'], $bookingId);

        $response = $this->request('/me/events/'.$bookingId_formatted, $token, 'GET', null);

        return $response->getBody();
    }
}
