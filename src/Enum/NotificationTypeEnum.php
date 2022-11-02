<?php

namespace App\Enum;

enum NotificationTypeEnum
{
    case SUCCESS;
    case FAILED;
    case CHANGED;
    case REQUEST_RECEIVED;
}
