<?php

namespace App\Message;

class WebformSubmitMessage
{
    private string $webformId;
    private string $submissionUuid;
    private string $sender;
    private string $getSubmissionUrl;
    private string $apiKeyUserId;

    public function __construct(string $webformId, string $submissionUuid, string $sender, string $getSubmissionUrl, string $apiKeyUserId)
    {
        $this->webformId = $webformId;
        $this->submissionUuid = $submissionUuid;
        $this->sender = $sender;
        $this->getSubmissionUrl = $getSubmissionUrl;
        $this->apiKeyUserId = $apiKeyUserId;
    }

    public function getWebformId(): string
    {
        return $this->webformId;
    }

    public function getSubmissionUuid(): string
    {
        return $this->submissionUuid;
    }

    public function getSender(): string
    {
        return $this->sender;
    }

    public function getSubmissionUrl(): string
    {
        return $this->getSubmissionUrl;
    }

    public function getApiKeyUserId(): string
    {
        return $this->apiKeyUserId;
    }
}
