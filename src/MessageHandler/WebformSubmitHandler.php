<?php

namespace App\MessageHandler;

use ApiPlatform\Exception\InvalidArgumentException;
use App\Entity\Main\Booking;
use App\Entity\Resources\AAKResource;
use App\Exception\WebformSubmissionRetrievalException;
use App\Message\CreateBookingMessage;
use App\Message\WebformSubmitMessage;
use App\Repository\Resources\AAKResourceRepository;
use App\Service\BookingServiceInterface;
use App\Service\Metric;
use App\Service\WebformServiceInterface;
use App\Utils\ValidationUtilsInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\RecoverableMessageHandlingException;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * @see https://github.com/itk-dev/os2forms_selvbetjening/blob/develop/web/modules/custom/os2forms_rest_api/README.md
 */
#[AsMessageHandler]
class WebformSubmitHandler
{
    public function __construct(
        private readonly WebformServiceInterface $webformService,
        private readonly MessageBusInterface $bus,
        private readonly ValidationUtilsInterface $validationUtils,
        private readonly LoggerInterface $logger,
        private readonly AAKResourceRepository $aakResourceRepository,
        private readonly Environment $twig,
        private readonly BookingServiceInterface $bookingService,
        private readonly Metric $metric,
    ) {
    }

    public function __invoke(WebformSubmitMessage $message): void
    {
        $this->metric->counter('invoke', null, $this);

        try {
            $dataSubmission = $this->webformService->getData($message);
        } catch (WebformSubmissionRetrievalException $e) {
            if ($e->getCode() == 403) {
                $this->metric->counter('forbiddenError', null, $this);
            }

            // TODO: Handle other request actions as a retryable exception.

            $this->metric->counter('generalUnrecoverableMessageHandlingException');
            $this->metric->counter('webformSubmissionRetrievalException', null, $this);
            $this->logger->error(sprintf('Webform submission handling failed: %d %s', $e->getCode(), $e->getMessage()));

            throw new UnrecoverableMessageHandlingException($e->getMessage());
        }

        try {
            $submissionsCount = count($dataSubmission['bookingData']);
            $this->logger->info("Webform submission data fetched. Setting up $submissionsCount CreateBooking jobs.");

            foreach ($dataSubmission['bookingData'] as $data) {
                $email = $this->validationUtils->validateEmail($data['resourceId']);

                /** @var AAKResource $resource */
                $resource = $this->aakResourceRepository->findOneBy(['resourceMail' => $email]);

                if (is_null($resource)) {
                    throw new WebformSubmissionRetrievalException('Resource does not exist', 404);
                }

                $body = $this->composeBookingContents($data, $resource, $dataSubmission['metaData'] ?? []);
                $htmlContents = $this->renderContentsAsHtml($body);

                $booking = new Booking();
                $booking->setBody($htmlContents);
                $booking->setUserName($data['name']);
                $booking->setUserMail($data['email']);
                $booking->setMetaData($dataSubmission['metaData'] ?? []);
                $booking->setSubject($data['subject'] ?? '');
                $booking->setResourceEmail($email);
                $booking->setResourceName($resource->getResourceName());
                $booking->setStartTime($this->validationUtils->validateDate($data['start']));
                $booking->setEndTime($this->validationUtils->validateDate($data['end']));
                $booking->setUserId($data['userId']);
                $booking->setUserPermission($data['userPermission']);
                $booking->setWhitelistKey($data['whitelistKey'] ?? null);

                $this->logger->info('Registering CreateBookingMessage job');

                // Register message to only dispatch if no exceptions are thrown in this handler
                // @see https://symfony.com/doc/current/messenger/dispatch_after_current_bus.html
                $message = new CreateBookingMessage($booking);
                $envelope = new Envelope($message);
                $this->bus->dispatch(
                    $envelope->with(new DispatchAfterCurrentBusStamp())
                );

                $this->metric->counter('create_booking_message_dispatched', 'Create booking message has been dispatched.', $this);
            }
        } catch (WebformSubmissionRetrievalException $e) {
            $this->metric->counter('generalUnrecoverableMessageHandlingException');
            $this->metric->counter('webformSubmissionRetrievalException', null, $this);

            $this->logger->error(sprintf('Webform submission handling failed: %d %s', $e->getCode(), $e->getMessage()));

            throw new UnrecoverableMessageHandlingException($e->getMessage());
        }
    }

    /**
     * @throws WebformSubmissionRetrievalException
     */
    private function composeBookingContents($data, AAKResource $resource, $metaData): array
    {
        try {
            $body = [];
            $body['resource'] = $resource;
            $body['submission'] = $data;
            $body['submission']['fromObj'] = new \DateTime($data['start']);
            $body['submission']['toObj'] = new \DateTime($data['end']);
            $body['metaData'] = $metaData;
            $body['userUniqueId'] = $this->bookingService->createBodyUserId($data['userId']);

            return $body;
        } catch (\Exception $exception) {
            $this->metric->counter('ERROR_composeBookingContents', null, $this);

            throw new WebformSubmissionRetrievalException($exception->getMessage());
        }
    }

    /**
     * @throws WebformSubmissionRetrievalException
     */
    private function renderContentsAsHtml(array $body): string
    {
        try {
            return $this->twig->render('booking.html.twig', $body);
        } catch (RuntimeError|SyntaxError|LoaderError $error) {
            $this->metric->counter('ERROR_renderContentsAsHtml', null, $this);

            throw new WebformSubmissionRetrievalException($error->getMessage());
        }
    }
}
