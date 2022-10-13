<?php

namespace App\Service;

enum NotificationTypeEnum
{
    case SUCCESS;
    case FAILED;
    case CHANGED;
}
