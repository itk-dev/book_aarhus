<?php

namespace App\Controller;

use App\Entity\Main\ApiKeyUser;
use App\Entity\Main\Booking;
use App\Entity\Resources\AAKResource;
use App\Exception\WebformSubmissionRetrievalException;
use App\Message\CreateBookingMessage;
use App\Repository\Resources\AAKResourceRepository;
use App\Service\MetricsHelper;
use App\Service\MicrosoftGraphBookingService;
use App\Utils\ValidationUtilsInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
        private readonly MetricsHelper $metricsHelper,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        // Algorithm:
        // Validate input.
        // Check for free intervals.
        // Create all booking.
        //


        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::INVOKE);

        $user = $this->getUser();
        if ($user instanceof ApiKeyUser) {
            $userId = $user->getId();
        }

        $content = $request->toArray();

        $p = 1;

        foreach ($content['bookings'] as $item) {
            $resourceId = $item['resourceId'];
            $resourceName = 'TODO';
            $body = 'TODO';
            $subject = $item['subject'];
            $startTime = $item['startTime'];
            $endTime = $item['endTime'];

        }



        $this->metricsHelper->incMethodTotal(__METHOD__, MetricsHelper::COMPLETE);

        return new Response(null, 201);
    }
}
