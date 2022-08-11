<?php

namespace App\MessageHandler;

use ApiPlatform\Core\Exception\InvalidArgumentException;
use App\Entity\Booking;
use App\Message\CreateBookingMessage;
use App\Message\WebformSubmitMessage;
use App\Repository\Main\ApiKeyUserRepository;
use App\Service\WebformServiceInterface;
use App\Utils\ValidationUtils;
use Psr\Log\LoggerInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @see https://github.com/itk-dev/os2forms_selvbetjening/blob/develop/web/modules/custom/os2forms_rest_api/README.md
 */
#[AsMessageHandler]
class WebformSubmitHandler
{
    public function __construct(private WebformServiceInterface $webformService, private ApiKeyUserRepository $apiKeyUserRepository, private MessageBusInterface $bus, private ValidationUtils $validationUtils, private LoggerInterface $logger)
    {
    }

    public function __invoke(WebformSubmitMessage $message): void
    {
        $this->logger->info('WebformSubmitHandler invoked.');

        $submissionUrl = $message->getSubmissionUrl();
        $userId = $message->getApiKeyUserId();

        $user = $this->apiKeyUserRepository->find($userId);

        if (!$user) {
            throw new UnrecoverableMessageHandlingException('ApiKeyUser not set.');
        }

        $this->logger->info("Fetching $submissionUrl");

        $webformSubmission = $this->webformService->getWebformSubmission($submissionUrl, $user->getWebformApiKey());

        try {
            $data = $this->webformService->getValidatedData($webformSubmission);
        } catch (Exception $e) {
            throw new UnrecoverableMessageHandlingException($e->getMessage());
        }

        $this->logger->info('Webform submission data fetched. Setting up CreateBooking job.');

        $body = [];
        $filterKeys = ['subject', 'resourceemail', 'resourcename', 'starttime', 'endtime', 'userid'];

        // Add extra fields to body.
        foreach ($data as $key => $datum) {
            if (!in_array($key, $filterKeys)) {
                $body[] = "$key: $datum";
            }
        }

        // Add userid to bottom of body.
        $userid = $data['userid'];
        $body[] = "[userid-$userid]";

        $bodyString = implode("\n", $body);

        try {
            $booking = new Booking();
            $booking->setBody($bodyString);
            $booking->setSubject($data['subject'] ?? '');
            $booking->setResourceEmail($this->validationUtils->validateEmail($data['resourceemail']));
            $booking->setResourceName($data['resourcename'] ?? '');
            $booking->setStartTime($this->validationUtils->validateDate($data['starttime'], 'Y-m-d\TH:i:sO'));
            $booking->setEndTime($this->validationUtils->validateDate($data['endtime'], 'Y-m-d\TH:i:sO'));

            $this->logger->info('Registering CreateBookingMessage job');

            // Register job.
            $this->bus->dispatch(new CreateBookingMessage($booking));
        } catch (InvalidArgumentException $e) {
            throw new UnrecoverableMessageHandlingException($e->getMessage());
        }
    }
}
