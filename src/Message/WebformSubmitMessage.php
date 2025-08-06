<?php

namespace App\Message;

class WebformSubmitMessage
{
    public function __construct(private readonly string $webformId, private readonly string $submissionUuid, private readonly string $sender, private readonly string $getSubmissionUrl, private readonly string $apiKeyUserId)
    {
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
