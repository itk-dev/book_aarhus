<?php

namespace App\MessageHandler;

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

        $webformSubmission = $this->getWebformSubmission($submissionUrl, $user->getApiKey());

        if (empty($webformSubmission['data'])) {
            throw new UnrecoverableMessageHandlingException('Webform data not set');
        }

        $data = $webformSubmission['data'];

        // TODO: Validate that required fields are present.

        $booking = new Booking();
        $booking->setBody($data['body']);
        $booking->setSubject($data['subject']);
        $booking->setResourceEmail($data['resourceEmail']);
        $booking->setResourceName($data['resourceName']);
        $booking->setStartTime($this->validationUtils->validateDate($data['startTime']));
        $booking->setEndTime($this->validationUtils->validateDate($data['endTime']));

        // Register job.
        $this->bus->dispatch(new CreateBookingMessage($booking));
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
