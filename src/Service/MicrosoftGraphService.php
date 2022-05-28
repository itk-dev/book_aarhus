<?php

namespace App\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Microsoft\Graph\Exception\GraphException;
use Microsoft\Graph\Graph;

/**
 * @see https://github.com/microsoftgraph/msgraph-sdk-php
 */
class MicrosoftGraphService
{
    public function __construct(private string $tenantId, private string $clientId, private string $serviceAccountUsername, private string $serviceAccountPassword)
    {
    }

    /**
     * @throws GuzzleException
     */
    public function authenticateAsApplication($clientSecret): string
    {
        $guzzle = new Client();
        $url = 'https://login.microsoftonline.com/'.$this->tenantId.'/oauth2/v2.0/token';

        $response = $guzzle->post($url, [
            'form_params' => [
                'client_id' => $this->clientId,
                'client_secret' => $clientSecret,
                'scope' => 'https://graph.microsoft.com/.default',
                'grant_type' => 'client_credentials',
            ],
        ]);

        $token = json_decode($response->getBody()->getContents());

        return $token->access_token;
    }

    /**
     * @throws GuzzleException
     */
    public function authenticateAsServiceAccount(): string
    {
        $guzzle = new Client();
        $url = 'https://login.microsoftonline.com/'.$this->tenantId.'/oauth2/v2.0/token';

        $response = $guzzle->post($url, [
            'form_params' => [
                'client_id' => $this->clientId,
                'scope' => 'https://graph.microsoft.com/.default',
                'username' => $this->serviceAccountUsername,
                'password' => $this->serviceAccountPassword,
                'grant_type' => 'password',
            ],
        ]);

        $token = json_decode($response->getBody()->getContents());

        return $token->access_token;
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
    public function request(string $endpoint, string $accessToken, string $requestType = 'GET')
    {
        $graph = new Graph();
        $graph->setAccessToken($accessToken);

        return $graph->createRequest($requestType, $endpoint)->execute();
    }
}
