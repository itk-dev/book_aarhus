<?php

namespace App\MessageHandler;

use ApiPlatform\Core\Exception\InvalidArgumentException;
use App\Entity\Booking;
use App\Message\CreateBookingMessage;
use App\Message\WebformSubmitMessage;
use App\Repository\ApiKeyUserRepository;
use App\Utils\ValidationUtils;
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
    public function __construct(private HttpClientInterface $client, private ApiKeyUserRepository $apiKeyUserRepository, private MessageBusInterface $bus, private ValidationUtils $validationUtils)
    {
    }

    public function __invoke(WebformSubmitMessage $message): void
    {
        $submissionUrl = $message->getSubmissionUrl();
        $userId = $message->getApiKeyUserId();

        $user = $this->apiKeyUserRepository->find($userId);

        if (!$user) {
            throw new UnrecoverableMessageHandlingException('ApiKeyUser not set.');
        }

        $webformSubmission = $this->getWebformSubmission($submissionUrl, $user->getWebformApiKey());

        if (empty($webformSubmission['data'])) {
            throw new UnrecoverableMessageHandlingException('Webform data not set');
        }

        $data = $webformSubmission['data'];

        // TODO: Validate that required fields are present.
        // TODO: Set extra fields as string in body.
        // TODO: Add unique user id to end of body on the form: [userid-xxx].

        try {
            $booking = new Booking();
            $booking->setBody($data['body']);
            $booking->setSubject($data['subject']);
            $booking->setResourceEmail($this->validationUtils->validateEmail($data['resourceEmail']));
            $booking->setResourceName($data['resourceName']);
            $booking->setStartTime($this->validationUtils->validateDate($data['startTime']));
            $booking->setEndTime($this->validationUtils->validateDate($data['endTime']));

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
