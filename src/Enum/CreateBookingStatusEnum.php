<?php

namespace App\Enum;

enum CreateBookingStatusEnum: string
{
    case REQUEST = 'REQUEST';
    case ERROR = 'ERROR';
    case CONFLICT = 'CONFLICT';
    case SUCCESS = 'SUCCESS';
    case CANCELLED = 'CANCELLED';
    case INVALID = 'INVALID';
}
