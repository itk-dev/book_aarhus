<?php

namespace App\MessageHandler;

use App\Message\CreateBookingWebformSubmitMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * @see https://github.com/itk-dev/os2forms_selvbetjening/blob/develop/web/modules/custom/os2forms_rest_api/README.md
 */
#[AsMessageHandler]
class CreateBookingWebformSubmitHandler
{
    public function __invoke(CreateBookingWebformSubmitMessage $message): void
    {

    }
}
