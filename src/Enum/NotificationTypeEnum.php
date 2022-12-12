<?php

namespace App\Enum;

enum NotificationTypeEnum
{
    case SUCCESS;
    case FAILED;
    case CHANGED;
    case REQUEST_RECEIVED;
    case CONFLICT;
    case DELETE_SUCCESS;
    case UPDATE_SUCCESS;
}
