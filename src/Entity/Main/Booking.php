<?php

namespace App\Entity\Main;

use ApiPlatform\Core\Annotation\ApiProperty;
use Symfony\Component\Uid\Ulid;

class Booking
{
    #[ApiProperty(identifier: true)]
    private string $id;

    private string $resourceEmail;

    private string $resourceName;

    private string $subject;

    private string $body;

    private \DateTime $startTime;

    private \DateTime $endTime;

    private string $webformSubmission;

    public function __construct()
    {
        $this->id = Ulid::generate();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getResourceEmail(): string
    {
        return $this->resourceEmail;
    }

    public function setResourceEmail(string $resourceEmail): void
    {
        $this->resourceEmail = $resourceEmail;
    }

    public function getResourceName(): string
    {
        return $this->resourceName;
    }

    public function setResourceName(string $resourceName): void
    {
        $this->resourceName = $resourceName;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): void
    {
        $this->subject = $subject;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function setBody(string $body): void
    {
        $this->body = $body;
    }

    public function getStartTime(): \DateTime
    {
        return $this->startTime;
    }

    public function setStartTime(\DateTime $startTime): void
    {
        $this->startTime = $startTime;
    }

    public function getEndTime(): \DateTime
    {
        return $this->endTime;
    }

    public function setEndTime(\DateTime $endTime): void
    {
        $this->endTime = $endTime;
    }

    public function getWebformSubmission(): string
    {
      return $this->webformSubmission;
    }

    public function setWebformSubmission(string $webformSubmission): void
    {
      $this->webformSubmission = $webformSubmission;
    }

}
