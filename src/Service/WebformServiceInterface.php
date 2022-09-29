<?php

namespace App\Service;

use App\Message\WebformSubmitMessage;

interface WebformServiceInterface
{
    public function getWebformSubmission(string $submissionUrl, string $webformApiKey): array;

    public function sortWebformSubmissionDataByType(array $webformSubmission): array;

    public function getData(WebformSubmitMessage $message): array;

    public function getValidatedData(array $webformSubmission): array;
}
