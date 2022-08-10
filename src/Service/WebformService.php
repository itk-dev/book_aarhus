<?php

namespace App\Service;

use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Messenger\Exception\RecoverableMessageHandlingException;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class WebformService implements WebformServiceInterface
{
    public function __construct(private HttpClientInterface $client)
    {
    }

    public function getWebformSubmission(string $submissionUrl, string $webformApiKey): array
    {
        try {
            $response = $this->client->request('GET', $submissionUrl, [
                'headers' => [
                    'api-key' => $webformApiKey,
                ],
            ]);

            return $response->toArray();
        } catch (HttpExceptionInterface|TransportExceptionInterface $e) {
            throw new RecoverableMessageHandlingException();
        } catch (DecodingExceptionInterface $e) {
            throw new UnrecoverableMessageHandlingException();
        }
    }

    public function getValidatedData(array $webformSubmission): array
    {
        // TODO: Adjust field requirements to booking array when it is ready in the webform.

        if (empty($webformSubmission['data'])) {
            throw new Exception('Webform data not set');
        }

        $data = $webformSubmission['data'];

        if (!isset($data['subject'])) {
            throw new Exception('Webform data.subject not set');
        }

        if (!isset($data['resourceemail'])) {
            throw new Exception('Webform data.resourceemail not set');
        }

        if (!isset($data['resourcename'])) {
            throw new Exception('Webform data.resourcename not set');
        }

        if (!isset($data['starttime'])) {
            throw new Exception('Webform data.starttime not set');
        }

        if (!isset($data['endtime'])) {
            throw new Exception('Webform data.endtime not set');
        }

        if (!isset($data['userid'])) {
            throw new Exception('Webform data.userid not set');
        }

        return $data;
    }
}
