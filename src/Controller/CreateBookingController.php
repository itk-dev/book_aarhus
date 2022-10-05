<?php

namespace App\Controller;

use App\Entity\Main\Booking;
use App\Message\CreateBookingMessage;
use App\Utils\ValidationUtils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsController]
class CreateBookingController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly ValidationUtils $validationUtils
    ) {
    }

    public function __invoke(Booking $data): Response
    {
        $this->validationUtils->validateEmail($data->getResourceEmail());

        // Register job.
        $this->bus->dispatch(new CreateBookingMessage($data));

        return new Response(null, 201);
    }
}
