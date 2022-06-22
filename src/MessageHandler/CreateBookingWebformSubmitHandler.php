<?php

namespace App\MessageHandler;

use App\Message\CreateBookingWebformSubmitMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class CreateBookingWebformSubmitHandler
{
    public function __invoke(CreateBookingWebformSubmitMessage $message): void
    {

    }
}
