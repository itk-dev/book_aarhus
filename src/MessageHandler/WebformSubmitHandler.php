<?php

namespace App\MessageHandler;

use ApiPlatform\Core\Exception\InvalidArgumentException;
use App\Entity\Main\Booking;
use App\Entity\Resources\AAKResource;
use App\Message\CreateBookingMessage;
use App\Message\WebformSubmitMessage;
use App\Repository\Main\AAKResourceRepository;
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
    public function __construct(
        private WebformServiceInterface $webformService,
        private ApiKeyUserRepository $apiKeyUserRepository,
        private MessageBusInterface $bus,
        private ValidationUtils $validationUtils,
        private LoggerInterface $logger,
        private AAKResourceRepository $aakResourceRepository,
    ) {
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
            $dataSubmissions = $this->webformService->getValidatedData($webformSubmission);
        } catch (Exception $e) {
            throw new UnrecoverableMessageHandlingException($e->getMessage());
        }

        $submissionsCount = count($dataSubmissions);
        $this->logger->info("Webform submission data fetched. Setting up $submissionsCount CreateBooking jobs.");

        $submissionKeys = array_keys($dataSubmissions);

        foreach ($dataSubmissions as $data) {
            $body = [];

            $filterKeys = $submissionKeys + ['subject', 'resourceId', 'start', 'end', 'userId'];
            $email = $this->validationUtils->validateEmail($data['resourceId']);

            /** @var AAKResource $resource */
            $resource = $this->aakResourceRepository->findOneBy(['resourceMail' => $email]);

            if (null == $resource) {
                throw new UnrecoverableMessageHandlingException("Resource $email not found.", 404);
            }

            // Add extra fields to body.
            foreach ($data as $key => $datum) {
                if (!in_array($key, $filterKeys)) {
                    $body[] = "$key: $datum";
                }
            }

            // Add userid to bottom of body.
            $userId = $data['userId'];
            $body[] = "[userid-$userId]";

            $bodyString = implode("\n", $body);

            try {
                $booking = new Booking();
                $booking->setBody($bodyString);
                $booking->setSubject($data['subject'] ?? '');
                $booking->setResourceEmail($email);
                $booking->setResourceName($resource->getResourceName());
                $booking->setStartTime($this->validationUtils->validateDate($data['start']));
                $booking->setEndTime($this->validationUtils->validateDate($data['end']));

                $this->logger->info('Registering CreateBookingMessage job');

                // Register job.
                $this->bus->dispatch(new CreateBookingMessage($booking));
            } catch (InvalidArgumentException $e) {
                throw new UnrecoverableMessageHandlingException($e->getMessage());
            }
        }
    }
}
