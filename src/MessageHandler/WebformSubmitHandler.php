<?php

namespace App\MessageHandler;

use App\Entity\Main\Booking;
use App\Entity\Main\Resource;
use App\Exception\WebformSubmissionRetrievalException;
use App\Interface\ValidationUtilsInterface;
use App\Interface\WebformServiceInterface;
use App\Message\CreateBookingMessage;
use App\Message\WebformSubmitMessage;
use App\Repository\ResourceRepository;
use App\Service\CreateBookingService;
use App\Service\MetricsHelper;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;

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
        private readonly ResourceRepository $aakResourceRepository,
        private readonly MetricsHelper $metricsHelper,
        private readonly CreateBookingService $createBookingService,
    ) {
    }

    public function __invoke(WebformSubmitMessage $message): void
    {
        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::INVOKE);

        try {
            $dataSubmission = $this->webformService->getData($message);
        } catch (WebformSubmissionRetrievalException $e) {
            if (403 == $e->getCode()) {
                $this->metricsHelper->incMethodTotal(__METHOD__, 'forbidden');
            }

            // TODO: Handle other request actions as a retryable exception.

            $this->logger->error(sprintf('Webform submission handling failed: %d %s', $e->getCode(), $e->getMessage()));
            $this->metricsHelper->incExceptionTotal(UnrecoverableMessageHandlingException::class);

            throw new UnrecoverableMessageHandlingException($e->getMessage());
        }

        try {
            $submissionsCount = count($dataSubmission['bookingData']);
            $this->logger->info("Webform submission data fetched. Setting up $submissionsCount CreateBooking jobs.");

            foreach ($dataSubmission['bookingData'] as $data) {
                $email = $this->validationUtils->validateEmail($data['resourceId']);

                /** @var Resource $resource */
                $resource = $this->aakResourceRepository->findOneBy(['resourceMail' => $email]);

                if (is_null($resource)) {
                    throw new WebformSubmissionRetrievalException('Resource does not exist', 404);
                }

                $body = $this->createBookingService->composeBookingContents($data, $resource, $dataSubmission['metaData'] ?? []);
                $htmlContents = $this->createBookingService->renderContentsAsHtml($body);

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
            }
        } catch (WebformSubmissionRetrievalException $e) {
            $this->logger->error(sprintf('Webform submission handling failed: %d %s', $e->getCode(), $e->getMessage()));
            $this->metricsHelper->incExceptionTotal(UnrecoverableMessageHandlingException::class);

            throw new UnrecoverableMessageHandlingException($e->getMessage());
        }

        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::COMPLETE);
    }
}
