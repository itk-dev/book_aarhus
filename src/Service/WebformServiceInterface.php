<?php

namespace App\Service;

interface WebformServiceInterface
{
    public function getWebformSubmission(string $submissionUrl, string $webformApiKey): array;

    public function getValidatedData(array $webformSubmission): array;
}
