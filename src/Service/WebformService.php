<?php

namespace App\Service;

use App\Message\WebformSubmitMessage;
use App\Repository\Main\ApiKeyUserRepository;
use JetBrains\PhpStorm\ArrayShape;
use Psr\Log\LoggerInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Messenger\Exception\RecoverableMessageHandlingException;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class WebformService implements WebformServiceInterface
{
    /**
     * WebformService constructor.
     *
     * @param HttpClientInterface $client
     * @param LoggerInterface $logger
     * @param ApiKeyUserRepository $apiKeyUserRepository
     */
    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly LoggerInterface $logger,
        private readonly ApiKeyUserRepository $apiKeyUserRepository,
    ) {
    }

    /**
     * @param string $submissionUrl
     * @param string $webformApiKey
     *
     * @return array
     */
    public function getWebformSubmission(string $submissionUrl, string $webformApiKey): array
    {
        try {
            $response = $this->client->request('GET', $submissionUrl, [
                'headers' => [
                    'api-key' => $webformApiKey,
                ],
            ]);

            return $response->toArray();
        } catch (HttpExceptionInterface|TransportExceptionInterface) {
            throw new RecoverableMessageHandlingException();
        } catch (DecodingExceptionInterface) {
            throw new UnrecoverableMessageHandlingException();
        }
    }

    /**
     * @param array $webformSubmission
     *
     * @return array[]
     */
    #[ArrayShape(['bookingData' => 'array', 'stringData' => 'array', 'arrayData' => 'array'])]
    public function sortWebformSubmissionDataByType(array $webformSubmission): array
    {
        $sortedData = [
            'bookingData' => [],
            'stringData' => [],
            'arrayData' => [],
        ];

        foreach ($webformSubmission['data'] as $key => $entry) {
            if (is_string($entry)) {
                try {
                    $data = json_decode(json: $entry, associative: true, flags: JSON_THROW_ON_ERROR);
                    if (is_array($data) && isset($data['formElement']) && 'booking_element' == $data['formElement']) {
                        $sortedData['bookingData'][$key] = $data;
                    } else {
                        $sortedData['stringData'][$key] = $entry;
                    }
                } catch (\Exception) {
                    $sortedData['stringData'][$key] = $entry;
                }
            } elseif (is_array($entry)) {
                $sortedData['arrayData'][$key] = $entry;
            }
        }

        return $sortedData;
    }

    /**
     * @param WebformSubmitMessage $message
     *
     * @return array
     */
    public function getData(WebformSubmitMessage $message): array
    {
        $this->logger->info('WebformSubmitHandler invoked.');

        $submissionUrl = $message->getSubmissionUrl();
        $apiKeyUserId = $message->getApiKeyUserId();

        $user = $this->apiKeyUserRepository->find($apiKeyUserId);

        if (!$user) {
            throw new UnrecoverableMessageHandlingException('ApiKeyUser not set.');
        }

        $this->logger->info("Fetching $submissionUrl");

        $webformSubmission = $this->getWebformSubmission($submissionUrl, $user->getWebformApiKey());

        try {
            $dataSubmission = $this->getValidatedData($webformSubmission);
        } catch (Exception $e) {
            throw new UnrecoverableMessageHandlingException($e->getMessage());
        }

        return $dataSubmission;
    }

    /**
     * @param array $webformSubmission
     *
     * @return array
     */
    public function getValidatedData(array $webformSubmission): array
    {
        if (empty($webformSubmission['data'])) {
            throw new Exception('Webform data not set');
        }
        $sortedData = $this->sortWebformSubmissionDataByType($webformSubmission);
        $acceptedSubmissions = [];

        foreach ($sortedData['bookingData'] as $key => $entry) {
            if (!isset($entry['subject'])) {
                throw new Exception("Webform ($key) subject not set");
            }

            if (!isset($entry['resourceId'])) {
                throw new Exception("Webform ($key) resourceId not set");
            }

            if (!isset($entry['start'])) {
                throw new Exception("Webform ($key) start not set");
            }

            if (!isset($entry['end'])) {
                throw new Exception("Webform ($key) end not set");
            }

            if (!isset($entry['name'])) {
                throw new Exception("Webform ($key) name not set");
            }

            if (!isset($entry['email'])) {
                throw new Exception("Webform ($key) email not set");
            }

            if (!isset($entry['userId'])) {
                throw new Exception("Webform ($key) userId not set");
            }

            if (!isset($entry['userPermission'])) {
                throw new Exception("Webform ($key) userPermission not set");
            }

            $acceptedSubmissions['bookingData'][$key] = $entry;
        }

        foreach ($sortedData['arrayData'] as $key => $entry) {
            $acceptedSubmissions['metaData'][$key] = implode(', ', $entry);
        }

        foreach ($sortedData['stringData'] as $key => $entry) {
            $acceptedSubmissions['metaData'][$key] = $entry;
        }

        if (0 == count($acceptedSubmissions['bookingData'])) {
            throw new Exception('No submission data found.');
        }

        return $acceptedSubmissions;
    }
}
