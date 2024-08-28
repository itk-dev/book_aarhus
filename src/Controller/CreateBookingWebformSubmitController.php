<?php

namespace App\Controller;

use App\Entity\Main\ApiKeyUser;
use App\Message\WebformSubmitMessage;
use App\Service\Metric;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsController]
class CreateBookingWebformSubmitController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly LoggerInterface $logger,
        private readonly Metric $metric,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $this->metric->incFunctionTotal($this, __FUNCTION__, Metric::INVOKE);

        $user = $this->getUser();
        if ($user instanceof ApiKeyUser) {
            $userId = $user->getId();
        }

        $webformContent = $request->toArray();
        $webformId = $webformContent['data']['webform']['id'] ?? null;
        $submissionUuid = $webformContent['data']['submission']['uuid'] ?? null;
        $sender = $webformContent['links']['sender'] ?? null;
        $getSubmissionUrl = $webformContent['links']['get_submission_url'] ?? null;
        $apiKeyUserId = $userId ?? $user?->getUserIdentifier();

        if (null === $webformId) {
            $this->metric->incExceptionTotal(BadRequestException::class);
            throw new BadRequestException('data->webform->id should not be null');
        }
        if (null === $submissionUuid) {
            $this->metric->incExceptionTotal(BadRequestException::class);
            throw new BadRequestException('data->submission->uuid should not be null');
        }
        if (null === $sender) {
            $this->metric->incExceptionTotal(BadRequestException::class);
            throw new BadRequestException('links->sender should not be null');
        }
        if (null === $getSubmissionUrl) {
            $this->metric->incExceptionTotal(BadRequestException::class);
            throw new BadRequestException('links->get_submission_url should not be null');
        }

        $this->logger->info('Registering WebformSubmitMessage job.');

        // Register job.
        $this->bus->dispatch(new WebformSubmitMessage(
            $webformId,
            $submissionUuid,
            $sender,
            $getSubmissionUrl,
            $apiKeyUserId ?? '',
        ));

        $this->metric->incFunctionTotal($this, __FUNCTION__, Metric::COMPLETE);

        return new Response(null, 201);
    }
}
