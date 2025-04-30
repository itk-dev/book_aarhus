<?php

namespace App\Service;

use App\Exception\WebformSubmissionRetrievalException;
use App\Interface\WebformServiceInterface;
use App\Message\WebformSubmitMessage;
use App\Repository\ApiKeyUserRepository;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class WebformService implements WebformServiceInterface
{
    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly LoggerInterface $logger,
        private readonly ApiKeyUserRepository $apiKeyUserRepository,
        private readonly MetricsHelper $metricsHelper,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getData(WebformSubmitMessage $message): array
    {
        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::INVOKE);

        $this->logger->info('WebformSubmitHandler.');

        $submissionUrl = $message->getSubmissionUrl();
        $apiKeyUserId = $message->getApiKeyUserId();

        $user = $this->apiKeyUserRepository->find($apiKeyUserId);

        if (!$user) {
            $this->metricsHelper->incExceptionTotal(WebformSubmissionRetrievalException::class);

            throw new WebformSubmissionRetrievalException('ApiKeyUser not set.');
        }

        $this->logger->info("Fetching $submissionUrl");

        $webformSubmission = $this->getWebformSubmission($submissionUrl, $user->getWebformApiKey());

        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::COMPLETE);

        return $this->getValidatedData($webformSubmission);
    }

    /**
     * {@inheritdoc}
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
        } catch (ExceptionInterface $exception) {
            $this->logger->error('getWebformSubmission Exception ('.$exception->getCode().'): '.$exception->getMessage());
            $this->metricsHelper->incExceptionTotal(WebformSubmissionRetrievalException::class);

            throw new WebformSubmissionRetrievalException($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * {@inheritdoc}
     */
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
     * {@inheritdoc}
     */
    public function getValidatedData(array $webformSubmission): array
    {
        if (empty($webformSubmission['data'])) {
            throw new WebformSubmissionRetrievalException('Webform data not set');
        }

        $sortedData = $this->sortWebformSubmissionDataByType($webformSubmission);
        $acceptedSubmissions = [
            'bookingData' => [],
        ];

        foreach ($sortedData['bookingData'] as $key => $entry) {
            if (!isset($entry['subject'])) {
                throw new WebformSubmissionRetrievalException("Webform ($key) subject not set");
            }

            if (!isset($entry['resourceId'])) {
                throw new WebformSubmissionRetrievalException("Webform ($key) resourceId not set");
            }

            if (!isset($entry['start'])) {
                throw new WebformSubmissionRetrievalException("Webform ($key) start not set");
            }

            if (!isset($entry['end'])) {
                throw new WebformSubmissionRetrievalException("Webform ($key) end not set");
            }

            if (!isset($entry['name'])) {
                throw new WebformSubmissionRetrievalException("Webform ($key) name not set");
            }

            if (!isset($entry['email'])) {
                throw new WebformSubmissionRetrievalException("Webform ($key) email not set");
            }

            if (!isset($entry['userId'])) {
                throw new WebformSubmissionRetrievalException("Webform ($key) userId not set");
            }

            if (!isset($entry['userPermission'])) {
                throw new WebformSubmissionRetrievalException("Webform ($key) userPermission not set");
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
            throw new WebformSubmissionRetrievalException('No submission data found.');
        }

        return $acceptedSubmissions;
    }
}
