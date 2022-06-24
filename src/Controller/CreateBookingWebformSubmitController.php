<?php

namespace App\Controller;

use App\Entity\ApiKeyUser;
use App\Message\WebformSubmitMessage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsController]
class CreateBookingWebformSubmitController extends AbstractController
{
    public function __construct(private MessageBusInterface $bus
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $webformContent = json_decode($request->getContent());

        $user = $this->getUser();

        if ($user instanceof ApiKeyUser) {
            $userId = $user->getId();
        }

        // TODO: Validate information.

        // Register job.
        $this->bus->dispatch(new WebformSubmitMessage(
            $webformContent->data->webform->id ?? null,
            $webformContent->data->submission->uuid ?? null,
            $webformContent->links->sender ?? null,
            $webformContent->links->get_submission_url ?? null,
            $userId ?? $user->getUserIdentifier() ?? null,
        ));

        return new Response(null, 201);
    }
}
