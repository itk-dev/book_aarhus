<?php

namespace App\MessageHandler;

use ApiPlatform\Core\Exception\InvalidArgumentException;
use App\Entity\Booking;
use App\Message\CreateBookingMessage;
use App\Message\WebformSubmitMessage;
use App\Repository\ApiKeyUserRepository;
use App\Utils\ValidationUtils;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\RecoverableMessageHandlingException;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @see https://github.com/itk-dev/os2forms_selvbetjening/blob/develop/web/modules/custom/os2forms_rest_api/README.md
 */
#[AsMessageHandler]
class WebformSubmitHandler
{
    public function __construct(private HttpClientInterface $client, private ApiKeyUserRepository $apiKeyUserRepository, private MessageBusInterface $bus, private ValidationUtils $validationUtils, private LoggerInterface $logger)
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

        // TODO: Remove this when url is correct from webform.
        $submissionUrl = str_replace('http://default/', 'http://selvbetjening_nginx_1.frontend/', $submissionUrl);

        $this->logger->info("Fetching $submissionUrl");

        $webformSubmission = $this->getWebformSubmission($submissionUrl, $user->getWebformApiKey());

        if (empty($webformSubmission['data'])) {
            throw new UnrecoverableMessageHandlingException('Webform data not set');
        }

        $data = $webformSubmission['data'];

        $this->logger->info('Webform submission data fetched. Setting up CreateBooking job.');
        $this->logger->info(json_encode($data));

        // TODO: Adjust field requirements to booking array when it is ready in the webform.

        if (!isset($data['subject'])) {
            throw new UnrecoverableMessageHandlingException('Webform data.subject not set');
        }

        if (!isset($data['resourceemail'])) {
            throw new UnrecoverableMessageHandlingException('Webform data.resourceemail not set');
        }

        if (!isset($data['resourcename'])) {
            throw new UnrecoverableMessageHandlingException('Webform data.resourcename not set');
        }

        if (!isset($data['starttime'])) {
            throw new UnrecoverableMessageHandlingException('Webform data.starttime not set');
        }

        if (!isset($data['endtime'])) {
            throw new UnrecoverableMessageHandlingException('Webform data.endtime not set');
        }

        if (!isset($data['userid'])) {
            throw new UnrecoverableMessageHandlingException('Webform data.userid not set');
        }

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
            throw new UnrecoverableMessageHandlingException('Invalid booking data.');
        }
    }

    private function getWebformSubmission(string $submissionUrl, string $webformApiKey): array
    {
        try {
            $response = $this->client->request('GET', $submissionUrl, [
                'headers' => [
                    'api-key' => $webformApiKey,
                ],
            ]);

            return $response->toArray();
        } catch (HttpExceptionInterface|TransportExceptionInterface $e) {
            throw new RecoverableMessageHandlingException();
        } catch (DecodingExceptionInterface $e) {
            throw new UnrecoverableMessageHandlingException();
        }
    }
}
