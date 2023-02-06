<?php

namespace App\Tests\Service;

class MicrosoftGraphBookingServiceData
{
    public static function getUserBookingData1(): array
    {
        return [
            '@odata.context' => 'https:\/\/graph.microsoft.com\/v1.0\/$metadata#users(\'test\')\/events\/$entity',
            '@odata.etag' => 'W\/\"TAG\"',
            'id' => 'ID123456',
            'createdDateTime' => '2022-12-13T11:58:15.4172798Z',
            'lastModifiedDateTime' => '2022-12-13T13:47:18.1110068Z',
            'changeKey' => '123456789',
            'categories' => [],
            'transactionId' => null,
            'originalStartTimeZone' => 'UTC',
            'originalEndTimeZone' => 'UTC',
            'iCalUId' => 'ICALUID12345678',
            'reminderMinutesBeforeStart' => 15,
            'isReminderOn' => false,
            'hasAttachments' => false,
            'subject' => 'Test Booking',
            'bodyPreview' => 'Preview',
            'importance' => 'normal',
            'sensitivity' => 'normal',
            'isAllDay' => false,
            'isCancelled' => false,
            'isOrganizer' => false,
            'responseRequested' => false,
            'seriesMasterId' => null,
            'showAs' => 'busy',
            'type' => 'singleInstance',
            'webLink' => "https:\/\/outlook.office365.com\/owa\/?itemid=test&exvsurl=1&path=\/calendar\/item",
            'onlineMeetingUrl' => null,
            'isOnlineMeeting' => false,
            'onlineMeetingProvider' => 'unknown',
            'allowNewTimeProposals' => false,
            'occurrenceId' => null,
            'isDraft' => false,
            'hideAttendees' => false,
            'responseStatus' => [
                'response' => 'accepted',
                'time' => '2022-12-13T11:58:15.4328965Z',
            ],
            'body' => [
                'contentType' => 'html',
                'content' => '<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width">
<meta name="x-apple-disable-message-reformatting">
</head>
<body>
<table role="presentation" cellspacing="0" cellpadding="24" border="0" align="center" class="document">
<tbody>
<tr>
<td valign="top">
<table role="presentation" cellspacing="0" cellpadding="24" border="0" align="center" width="750" class="container" style="background-color:#ffffff">
<tbody>
<tr>
<td>
<table class="booking-fields" role="presentation" aria-hidden="true" cellspacing="0" cellpadding="0" border="0" align="center" width="100%">
<tbody>
<tr>
<td>
<table class="header" role="presentation" cellspacing="0" cellpadding="6" border="0" align="left" width="100%">
<tbody>
<tr>
<td>
<h2>Ny booking af DOKK1-Lokale-Test1 - LOCATION1</h2>
</td>
</tr>
</tbody>
</table>
<table class="resource zebra" cellspacing="0" cellpadding="6" border="0" align="left" width="100%" style="border-bottom:1px solid #E9ECEF">
<tbody>
<tr>
<td>
<h3>Resource</h3>
</td>
</tr>
<tr>
<td>Resource navn:</td>
<td class="hasData" id="resourceName">DOKK1-Lokale-Test1</td>
</tr>
<tr>
<td>Resource email:</td>
<td class="hasData" id="resourceMail">DOKK1-Lokale-Test1@aarhus.dk</td>
</tr>
</tbody>
</table>
<table class="container" role="presentation" aria-hidden="true" cellspacing="0" cellpadding="12" border="0" align="center" width="750">
<tbody>
<tr>
<td></td>
</tr>
</tbody>
</table>
<table class="booking zebra" cellspacing="0" cellpadding="6" border="0" align="left" width="100%" style="border-bottom:1px solid #E9ECEF">
<tbody>
<tr>
<td>
<h3>Booking</h3>
</td>
</tr>
<tr class="subject">
<td>Booking emne:</td>
<td class="hasData" id="subject">E1</td>
</tr>
<tr class="start-time">
<td>Booking starttidspunkt:</td>
<td>13/12/2022 - 14:00<span id="start" class="hidden hasData">2022-12-13T14:00:00.000Z</span></td>
</tr>
<tr class="end-time">
<td>Booking sluttidspunkt:</td>
<td>13/12/2022 - 14:15<span id="end" class="hidden hasData">2022-12-13T14:15:00.000Z</span></td>
</tr>
<tr class="extra">
<td>extra</td>
<td id="extra" class="metaData hasData">asd</td>
</tr>
</tbody>
</table>
<table class="container" role="presentation" aria-hidden="true" cellspacing="0" cellpadding="12" border="0" align="center" width="750">
<tbody>
<tr>
<td></td>
</tr>
</tbody>
</table>
<table class="user zebra" cellspacing="0" cellpadding="6" border="0" align="left" width="100%" style="border-bottom:1px solid #E9ECEF">
<tbody>
<tr>
<td>
<h3>Bruger</h3>
</td>
</tr>
<tr class="booker-name">
<td>Booking bruger navn:</td>
<td id="name" class="hasData">Test Testesen</td>
</tr>
<tr class="booker-email">
<td>Booking bruger email:</td>
<td><a href="mailto:test@example.com?subject=Vdr. Booking af DOKK1-Lokale-Test1 - LOCATION1" id="email" class="hasData">test@example.com</a></td>
</tr>
<tr class="user-id">
<td>Booking bruger id:</td>
<td id="userId" class="hasData">UID-1234567890-UID</td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>
<table class="container" role="presentation" aria-hidden="true" cellspacing="0" cellpadding="12" border="0" align="center" width="750">
<tbody>
<tr>
<td></td>
</tr>
</tbody>
</table>
<table class="container" role="presentation" cellspacing="0" cellpadding="6" border="0" align="center" width="750">
<tbody>
<tr>
<td>Denne mail er sendt fra <a href="https://booking.aarhus.dk">Book Aarhus.</a> </td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>
</body>
</html>',
            ],
            'start' => ['dateTime' => '2022-12-13T14:00:00.0000000', 'timeZone' => 'UTC'],
            'end' => ['dateTime' => '2022-12-13T14:15:00.0000000', 'timeZone' => 'UTC'],
            'location' => ['displayName' => 'DOKK1-Lokale-Test1', 'locationType' => 'default', 'uniqueId' => 'DOKK1-Lokale-Test1', 'uniqueIdType' => 'private'],
            'locations' => [['displayName' => 'DOKK1-Lokale-Test1', 'locationType' => 'default', 'uniqueId' => 'DOKK1-Lokale-Test1', 'uniqueIdType' => 'private']],
            'recurrence' => null,
            'attendees' => [
                ['type' => 'required', 'status' => ['response' => 'none', 'time' => '0001-01-01T00:00:00Z'], 'emailAddress' => ['name' => 'DOKK1-Lokale-Test1', 'address' => 'DOKK1-Lokale-Test1@aarhus.dk']],
                ['type' => 'optional', 'status' => ['response' => 'accepted', 'time' => '0001-01-01T00:00:00Z'], 'emailAddress' => ['name' => 'SERVICEACCOUNT', 'address' => 'serviceaccount@example.com']],
            ],
            'organizer' => ['emailAddress' => ['name' => 'DOKK1-Lokale-Test1', 'address' => 'DOKK1-Lokale-Test1@aarhus.dk']],
            'onlineMeeting' => null,
            'calendar@odata.associationLink' => 'https:\/\/graph.microsoft.com\/v1.0\/users(\'test\')\/calendars(\'test\')\/$ref',
            'calendar@odata.navigationLink' => 'https:\/\/graph.microsoft.com\/v1.0\/users(\'test\')\/calendars(\'test\')',
        ];
    }

    public static function getUserBookingData2(): array
    {
        $arr = self::getUserBookingData1();
        $arr['start'] = ['dateTime' => '2042-12-13T14:00:00.0000000', 'timeZone' => 'UTC'];
        $arr['end'] = ['dateTime' => '2042-12-13T14:15:00.0000000', 'timeZone' => 'UTC'];
        $arr['id'] = 'ID123456';

        return $arr;
    }

    public static function getUserBookingData3(): array
    {
        $arr = self::getUserBookingData1();
        $arr['id'] = 'ID3';
        $arr['iCalUId'] = 'ICALUID3';

        return $arr;
    }

    public static function getUserBookings1(): array
    {
        return [
            'value' => [
                [
                    'searchTerms' => ['uid-1234567890-uid'],
                    'hitsContainers' => [
                        [
                            'hits' => [
                                ['hitId' => '123'],
                                ['hitId' => '124'],
                                ['hitId' => '125'],
                                ['hitId' => '126'],
                                ['hitId' => '127'],
                            ],
                            'total' => 6,
                            'moreResultsAvailable' => true,
                        ],
                    ],
                ],
            ],
        ];
    }

    public static function getUserBookings2(): array
    {
        return [
            'value' => [
                [
                    'searchTerms' => ['uid-1234567890-uid'],
                    'hitsContainers' => [
                        [
                            'hits' => [
                                ['hitId' => '128'],
                                ['hitId' => '129'],
                            ],
                            'total' => 7,
                            'moreResultsAvailable' => false,
                        ],
                    ],
                ],
            ],
        ];
    }
}
