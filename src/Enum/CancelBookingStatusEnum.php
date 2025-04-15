<?php

namespace App\Enum;

enum CancelBookingStatusEnum: string
{
    case DELETED = 'DELETED';
    case UNRESOLVED = 'UNRESOLVED';
    case ERROR = 'ERROR';
    case NOT_FOUND = 'NOT_FOUND';
    case FORBIDDEN = 'FORBIDDEN';
}
