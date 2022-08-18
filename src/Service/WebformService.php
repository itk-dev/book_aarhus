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
        if (empty($webformSubmission['data'])) {
            throw new Exception('Webform data not set');
        }

        $acceptedSubmissions = [];

        foreach ($webformSubmission['data'] as $key => $entry) {
            try {
                $data = json_decode(json: $entry, associative: true, flags: JSON_THROW_ON_ERROR);

                // Only handle fields that are json encoded and contain the formElement property with value booking_element.
                if ('booking_element' == $data['formElement']) {
                    // Enforce required fields.

                    if (!isset($data['subject'])) {
                        throw new Exception("Webform ($key) subject not set");
                    }

                    if (!isset($data['resourceEmail'])) {
                        throw new Exception("Webform ($key) resourceEmail not set");
                    }

                    if (!isset($data['startTime'])) {
                        throw new Exception("Webform ($key) startTime not set");
                    }

                    if (!isset($data['endTime'])) {
                        throw new Exception("Webform ($key) endTime not set");
                    }

                    if (!isset($data['authorName'])) {
                        throw new Exception("Webform ($key) authorName not set");
                    }

                    if (!isset($data['authorEmail'])) {
                        throw new Exception("Webform ($key) authorEmail not set");
                    }

                    if (!isset($data['userId'])) {
                        throw new Exception("Webform ($key) userId not set");
                    }

                    $acceptedSubmissions[$key] = $data;
                }
            } catch (\JsonException) {
                // Ignore if the property can not be parsed.
            }
        }

        if (0 == count($acceptedSubmissions)) {
            throw new Exception('No submission data found.');
        }

        return $acceptedSubmissions;
    }
}
