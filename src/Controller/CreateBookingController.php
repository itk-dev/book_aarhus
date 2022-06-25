<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Message\CreateBookingMessage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsController]
class CreateBookingController extends AbstractController
{
    public function __construct(private MessageBusInterface $bus
    ) {
    }

    public function __invoke(Booking $data): Response
    {
        // Register job.
        $this->bus->dispatch(new CreateBookingMessage($data));

        return new Response(null, 201);
    }
}
