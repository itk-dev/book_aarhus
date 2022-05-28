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
class MicrosoftGraphService
{
    public function __construct(private string $tenantId, private string $clientId, private string $serviceAccountUsername, private string $serviceAccountPassword)
    {
    }

    /**
     * @throws GuzzleException
     */
    public function authenticateAsServiceAccount(): string
    {
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
    public function getFreeBusy(array $schedules, \DateTime $startTime, \DateTime $endTime): array
    {
        $token = $this->authenticateAsServiceAccount();

        // see https://docs.microsoft.com/en-us/graph/api/resources/datetimetimezone?view=graph-rest-1.0
        // example 2019-03-15T09:00:00
        $format = 'Y-m-d\TH:i:s';

        $body = [
            'schedules' => $schedules,
            'startTime' => [
                'dateTime' => $startTime->setTimezone(new \DateTimeZone('UTC'))->format($format),
                'timeZone' => 'UTC',
            ],
            'endTime' => [
                'dateTime' => $endTime->setTimezone(new \DateTimeZone('UTC'))->format($format),
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
}
