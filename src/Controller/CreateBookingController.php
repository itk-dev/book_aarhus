<?php

namespace App\Controller;

use App\Dto\CreateBookingsInput;
use App\Entity\Main\ApiKeyUser;
use App\Entity\Main\Booking;
use App\Entity\Resources\AAKResource;
use App\Exception\WebformSubmissionRetrievalException;
use App\Message\CreateBookingMessage;
use App\Message\WebformSubmitMessage;
use App\Repository\Resources\AAKResourceRepository;
use App\Service\MetricsHelper;
use App\Utils\ValidationUtilsInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[AsController]
class CreateBookingController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly LoggerInterface $logger,
        private readonly MetricsHelper $metricsHelper,
        private readonly ValidationUtilsInterface $validationUtils,
        private readonly AAKResourceRepository $aakResourceRepository,
        private readonly Environment $twig,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::INVOKE);

        $user = $this->getUser();
        if ($user instanceof ApiKeyUser) {
            $userId = $user->getId();
        }

        $content = $request->toArray();

        $resourceEmail = $content['resourceEmail'];


        $response = $this->bookingService->createBookingForResource(
            $booking->getResourceEmail(),
            $booking->getResourceName(),
            $booking->getSubject(),
            $booking->getBody(),
            $booking->getStartTime(),
            $booking->getEndTime(),
            $acceptConflict,
        );

        // Create booking.
        // Return result.

        $submissionsCount = count($content['bookingData']);
        $this->logger->info("Webform submission data fetched. Setting up $submissionsCount CreateBooking jobs.");

        foreach ($content['bookingData'] as $data) {
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
            $booking->setSubject($data['subject'] ?? '');
            $booking->setResourceEmail($email);
            $booking->setResourceName($resource->getResourceName());
            $booking->setStartTime($this->validationUtils->validateDate($data['start']));
            $booking->setEndTime($this->validationUtils->validateDate($data['end']));
            $booking->setUserId($data['userId']);
            $booking->setUserPermission($data['userPermission']);

            $this->logger->info('Registering CreateBookingMessage job');

            // Register message to only dispatch if no exceptions are thrown in this handler
            // @see https://symfony.com/doc/current/messenger/dispatch_after_current_bus.html
            $message = new CreateBookingMessage($booking);
            $envelope = new Envelope($message);
            $this->bus->dispatch(
                $envelope->with(new DispatchAfterCurrentBusStamp())
            );
        }

        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::COMPLETE);

        return new Response(null, 201);
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
            $this->metricsHelper->incExceptionTotal(\Exception::class);

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
            $this->metricsHelper->incExceptionTotal(\Error::class);

            throw new WebformSubmissionRetrievalException($error->getMessage());
        }
    }
}
