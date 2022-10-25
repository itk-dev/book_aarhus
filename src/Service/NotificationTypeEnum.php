<?php

namespace App\Service;

enum NotificationTypeEnum
{
    case SUCCESS;
    case FAILED;
    case CHANGED;
    case REQUEST_RECEIVED;
}
