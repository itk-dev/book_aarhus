<?php

namespace App\Service;

use App\Exception\WebformSubmissionRetrievalException;
use App\Message\WebformSubmitMessage;

interface WebformServiceInterface
{
    /**
     * Get submission data.
     *
     * @param WebformSubmitMessage $message webform submit message
     *
     * @return array
     *
     * @throws WebformSubmissionRetrievalException
     */
    public function getData(WebformSubmitMessage $message): array;

    /**
     * Get webform submission data from the given submissionUrl and webformApiKey.
     *
     * @param string $submissionUrl Url to retrieve
     * @param string $webformApiKey Apikey for the Drupal user
     *
     * @return array
     *
     * @throws WebformSubmissionRetrievalException
     */
    public function getWebformSubmission(string $submissionUrl, string $webformApiKey): array;

    /**
     * Sort webform submission data by type.
     *
     * @param array $webformSubmission
     *
     * @return array
     */
    public function sortWebformSubmissionDataByType(array $webformSubmission): array;

    /**
     * Get validated data from a webform submission.
     *
     * @param array $webformSubmission the webform submission data
     *
     * @return array
     *
     * @throws WebformSubmissionRetrievalException
     */
    public function getValidatedData(array $webformSubmission): array;
}
