<?php

namespace App\Service;

use App\Exception\MicrosoftGraphCommunicationException;
use App\Factory\ClientFactory;
use GuzzleHttp\Exception\GuzzleException;
use Microsoft\Graph\Exception\GraphException;
use Microsoft\Graph\Http\GraphResponse;
use Psr\Cache\CacheItemInterface;
use Symfony\Contracts\Cache\CacheInterface;

class MicrosoftGraphHelperService
{
    public function __construct(
        private readonly string $tenantId,
        private readonly string $clientId,
        private readonly string $serviceAccountUsername,
        private readonly string $serviceAccountPassword,
        private readonly CacheInterface $graphCache,
        private readonly ClientFactory $clientFactory,
        private readonly Metric $metric,
    ) {
    }

    public function authenticateAsServiceAccount(): string
    {
        return $this->graphCache->get('serviceAccountToken', function (CacheItemInterface $item) {
            $tokenEntry = $this->authenticateAsUser($this->serviceAccountUsername, $this->serviceAccountPassword);

            $item->expiresAfter($tokenEntry['expires_in'] ?? 3600);

            return $tokenEntry['access_token'];
        });
    }

    /**
     * @throws MicrosoftGraphCommunicationException
     */
    public function authenticateAsUser(string $username, string $password): array
    {
        try {
            $client = $this->clientFactory->getGuzzleClient();
            $url = 'https://login.microsoftonline.com/'.$this->tenantId.'/oauth2/v2.0/token';

            $response = $client->post($url, [
                'form_params' => [
                    'client_id' => $this->clientId,
                    'scope' => 'https://graph.microsoft.com/.default',
                    'username' => $username,
                    'password' => $password,
                    'grant_type' => 'password',
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException|GuzzleException $exception) {
            throw new MicrosoftGraphCommunicationException($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @throws MicrosoftGraphCommunicationException
     */
    public function request(string $path, string $accessToken, string $requestType = 'GET', array $body = null): GraphResponse
    {
        $this->metric->incMethodTotal(__METHOD__, Metric::INVOKE);

        try {
            $graph = $this->clientFactory->getGraph();
            $graph->setAccessToken($accessToken);

            $graphRequest = $graph->createRequest($requestType, $path);

            if ($body) {
                $graphRequest->attachBody($body);
            }

            $this->metric->incMethodTotal(__METHOD__, Metric::COMPLETE);

            return $graphRequest->execute();
        } catch (GuzzleException|GraphException $e) {
            throw new MicrosoftGraphCommunicationException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Check that there is no interval conflict.
     *
     * @param string $resourceEmail resource to check for conflict in
     * @param \DateTime $startTime start of interval
     * @param \DateTime $endTime end of interval
     * @param string|null $accessToken access token
     * @param array|null $ignoreICalUIds Ignore bookings with these ICalUIds in the evaluation. Use to allow editing an existing booking.
     *
     * @return bool whether there is a booking conflict for the given interval
     *
     * @throws MicrosoftGraphCommunicationException
     */
    public function isBookingConflict(string $resourceEmail, \DateTime $startTime, \DateTime $endTime, string $accessToken = null, array $ignoreICalUIds = null): bool
    {
        $token = $accessToken ?: $this->authenticateAsServiceAccount();
        $startString = $startTime->setTimezone(new \DateTimeZone('UTC'))->format(MicrosoftGraphBookingService::DATE_FORMAT).'Z';
        $endString = $endTime->setTimezone(new \DateTimeZone('UTC'))->format(MicrosoftGraphBookingService::DATE_FORMAT).'Z';

        $filterString = "\$filter=start/dateTime lt '$endString' and end/dateTime gt '$startString'";

        $response = $this->request("/users/$resourceEmail/calendar/events?$filterString", $token);

        $body = $response->getBody();

        $entries = $body['value'];

        if (count($entries) > 0) {
            if (null != $ignoreICalUIds) {
                foreach ($entries as $entry) {
                    if (!in_array($entry['iCalUId'], $ignoreICalUIds)) {
                        return true;
                    }
                }
            } else {
                return true;
            }
        }

        return false;
    }
}
