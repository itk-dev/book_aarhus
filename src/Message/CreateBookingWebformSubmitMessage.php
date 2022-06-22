<?php

namespace App\Message;

class CreateBookingWebformSubmitMessage
{
    private string $webformId;
    private string $submissionUuid;
    private string $sender;
    private string $getSubmissionUrl;

    public function __construct(string $webformId, string $submissionUuid, string $sender, string $getSubmissionUrl)
    {
        $this->webformId = $webformId;
        $this->submissionUuid = $submissionUuid;
        $this->sender = $sender;
        $this->getSubmissionUrl = $getSubmissionUrl;
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
}
