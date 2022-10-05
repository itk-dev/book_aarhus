<?php

namespace App\MessageHandler;

use ApiPlatform\Core\Exception\InvalidArgumentException;
use App\Entity\Main\Booking;
use App\Entity\Resources\AAKResource;
use App\Message\CreateBookingMessage;
use App\Message\WebformSubmitMessage;
use App\Repository\Main\AAKResourceRepository;
use App\Service\WebformServiceInterface;
use App\Utils\ValidationUtilsInterface;
use DateTime;
use Psr\Log\LoggerInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\MessageBusInterface;
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
    ) {
    }

    /**
     * @param WebformSubmitMessage $message
     *
     * @throws \Exception
     */
    public function __invoke(WebformSubmitMessage $message): void
    {
        $dataSubmission = $this->webformService->getData($message);
        $submissionsCount = count($dataSubmission['bookingData']);
        $this->logger->info("Webform submission data fetched. Setting up $submissionsCount CreateBooking jobs.");

        foreach ($dataSubmission['bookingData'] as $data) {
            $email = $this->validationUtils->validateEmail($data['resourceId']);

            /** @var AAKResource $resource */
            $resource = $this->aakResourceRepository->findOneBy(['resourceMail' => $email]);

            try {
                $body = $this->composeBookingContents($data, $email, $resource, $dataSubmission['metaData']);
                $htmlContents = $this->renderContentsAsHtml($body);

                $booking = new Booking();
                $booking->setBody($htmlContents);
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

    /**
     * @param $data
     * @param $email
     * @param $resource
     * @param $metaData
     *
     * @return array
     *
     * @throws Exception|\Exception
     */
    private function composeBookingContents($data, $email, $resource, $metaData): array
    {
        $body = [];

        if (null == $resource) {
            throw new UnrecoverableMessageHandlingException("Resource $email not found.", 404);
        }

        $body['resource'] = $resource;
        $body['submission'] = $data;
        $body['submission']['fromObj'] = new DateTime($data['start']);
        $body['submission']['toObj'] = new DateTime($data['end']);
        $body['metaData'] = $metaData;

        return $body;
    }

    /**
     * @param $body
     *
     * @return string
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    private function renderContentsAsHtml($body): string
    {
        return $this->twig->render('booking.html.twig', $body);
    }
}
