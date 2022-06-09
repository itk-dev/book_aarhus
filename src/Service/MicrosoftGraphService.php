<?php

namespace App\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Microsoft\Graph\Exception\GraphException;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Http\GraphResponse;

/**
 * @see https://github.com/microsoftgraph/msgraph-sdk-php
 * @see https://docs.microsoft.com/en-us/graph/use-the-api
 */
class MicrosoftGraphService implements MicrosoftGraphServiceInterface
{
    // see https://docs.microsoft.com/en-us/graph/api/resources/datetimetimezone?view=graph-rest-1.0
    // example 2019-03-15T09:00:00
    public const DATE_FORMAT = 'Y-m-d\TH:i:s';

    public function __construct(private string $tenantId, private string $clientId, private string $serviceAccountUsername, private string $serviceAccountPassword)
    {
    }

    /**
     * @throws GuzzleException
     */
    public function authenticateAsServiceAccount(): string
    {
        // TODO: Store access token until it expires to avoid unnecessary authentication calls.

        return $this->authenticateAsUser($this->serviceAccountUsername, $this->serviceAccountPassword);
    }

    /**
     * @throws GuzzleException
     */
    public function authenticateAsUser($username, $password): string
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

        $token = json_decode($response->getBody()->getContents());

        return $token->access_token;
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
     * @throws GuzzleException|GraphException
     *
     * @see https://docs.microsoft.com/en-us/graph/api/calendar-getschedule?view=graph-rest-1.0&tabs=http
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
     * @throws GuzzleException|GraphException
     *
     * @see https://docs.microsoft.com/en-us/graph/api/user-post-events?view=graph-rest-1.0&tabs=http#examples
     */
    public function createBooking(string $resourceEmail, string $resourceName, string $subject, string $body, \DateTime $startTime, \DateTime $endTime): array
    {
        $token = $this->authenticateAsServiceAccount();

        $body = [
            'subject' => $subject,
            'body' => [
                'contentType' => 'text',
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
            ],
            'attendees' => [
                [
                    'emailAddress' => [
                        'address' => $resourceEmail,
                        'name' => $resourceName,
                    ],
                    'type' => 'required',
                ],
            ],
        ];

        $response = $this->request('/me/events', $token, 'POST', $body);

        return $response->getBody();
    }

    /**
     * @throws GuzzleException|GraphException
     *
     * @see https://docs.microsoft.com/en-us/graph/search-concept-events
     */
    public function getUserBookings(string $userId): array
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
}
