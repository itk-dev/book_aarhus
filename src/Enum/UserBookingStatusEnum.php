<?php

namespace App\Enum;

/**
 * @see https://learn.microsoft.com/en-us/graph/api/resources/responsestatus?view=graph-rest-1.0
 */
enum UserBookingStatusEnum
{
    case ACCEPTED;
    case DECLINED;
    case NONE;
    case AWAITING_APPROVAL;
}
