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
                'content' => '<html>\r\n<head><\/head>\r\n<body>\r\n<table role=\"presentation\" cellspacing=\"0\" cellpadding=\"24\" border=\"0\" align=\"center\" class=\"document\">\r\n<tbody>\r\n<tr>\r\n<td valign=\"top\">\r\n<table role=\"presentation\" cellspacing=\"0\" cellpadding=\"24\" border=\"0\" align=\"center\" width=\"750\" class=\"container\" style=\"background-color:#ffffff\">\r\n<tbody>\r\n<tr>\r\n<td>\r\n<table class=\"booking-fields\" role=\"presentation\" aria-hidden=\"true\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" align=\"center\" width=\"100%\">\r\n<tbody>\r\n<tr>\r\n<td>\r\n<table class=\"header\" role=\"presentation\" cellspacing=\"0\" cellpadding=\"6\" border=\"0\" align=\"left\" width=\"100%\">\r\n<tbody>\r\n<tr>\r\n<td>\r\n<h2>Ny booking af DOKK1-Lokale-Test1 - LOCATION1<\/h2>\r\n<\/td>\r\n<\/tr>\r\n<\/tbody>\r\n<\/table>\r\n<table class=\"resource zebra\" cellspacing=\"0\" cellpadding=\"6\" border=\"0\" align=\"left\" width=\"100%\" style=\"border-bottom:1px solid #E9ECEF\">\r\n<tbody>\r\n<tr>\r\n<td>\r\n<h3>Resource<\/h3>\r\n<\/td>\r\n<\/tr>\r\n<tr>\r\n<td>Resource navn:<\/td>\r\n<td class=\"hasData\" id=\"resourceName\">DOKK1-Lokale-Test1<\/td>\r\n<\/tr>\r\n<tr>\r\n<td>Resource email:<\/td>\r\n<td class=\"hasData\" id=\"resourceMail\">DOKK1-Lokale-Test1@aarhus.dk<\/td>\r\n<\/tr>\r\n<\/tbody>\r\n<\/table>\r\n<table class=\"container\" role=\"presentation\" aria-hidden=\"true\" cellspacing=\"0\" cellpadding=\"12\" border=\"0\" align=\"center\" width=\"750\">\r\n<tbody>\r\n<tr>\r\n<td><\/td>\r\n<\/tr>\r\n<\/tbody>\r\n<\/table>\r\n<table class=\"booking zebra\" cellspacing=\"0\" cellpadding=\"6\" border=\"0\" align=\"left\" width=\"100%\" style=\"border-bottom:1px solid #E9ECEF\">\r\n<tbody>\r\n<tr>\r\n<td>\r\n<h3>Booking<\/h3>\r\n<\/td>\r\n<\/tr>\r\n<tr class=\"subject\">\r\n<td>Booking emne:<\/td>\r\n<td class=\"hasData\" id=\"subject\">Test booking<\/td>\r\n<\/tr>\r\n<tr class=\"start-time\">\r\n<td>Booking starttidspunkt:<\/td>\r\n<td>13\/12\/2022 - 14:00<span id=\"start\" class=\"hidden hasData\">2022-12-13T14:00:00.000Z<\/span><\/td>\r\n<\/tr>\r\n<tr class=\"end-time\">\r\n<td>Booking sluttidspunkt:<\/td>\r\n<td>13\/12\/2022 - 14:15<span id=\"end\" class=\"hidden hasData\">2022-12-13T14:15:00.000Z<\/span><\/td>\r\n<\/tr>\r\n<tr class=\"extra\">\r\n<td>extra<\/td>\r\n<td id=\"extra\" class=\"metaData hasData\">extradata<\/td>\r\n<\/tr>\r\n<\/tbody>\r\n<\/table>\r\n<table class=\"container\" role=\"presentation\" aria-hidden=\"true\" cellspacing=\"0\" cellpadding=\"12\" border=\"0\" align=\"center\" width=\"750\">\r\n<tbody>\r\n<tr>\r\n<td><\/td>\r\n<\/tr>\r\n<\/tbody>\r\n<\/table>\r\n<table class=\"user zebra\" cellspacing=\"0\" cellpadding=\"6\" border=\"0\" align=\"left\" width=\"100%\" style=\"border-bottom:1px solid #E9ECEF\">\r\n<tbody>\r\n<tr>\r\n<td>\r\n<h3>Bruger<\/h3>\r\n<\/td>\r\n<\/tr>\r\n<tr class=\"booker-name\">\r\n<td>Booking bruger navn:<\/td>\r\n<td id=\"name\" class=\"hasData\">Test Testesen<\/td>\r\n<\/tr>\r\n<tr class=\"booker-email\">\r\n<td>Booking bruger email:<\/td>\r\n<td><a href=\"mailto:test@example.com?subject=Vdr. Booking af DOKK1-Lokale-Test1 - LOCATION1\" id=\"email\" class=\"hasData\">test@example.com<\/a><\/td>\r\n<\/tr>\r\n<tr class=\"user-id\">\r\n<td>Booking bruger id:<\/td>\r\n<td id=\"userId\" class=\"hasData\">UID-1234567890-UID<\/td>\r\n<\/tr>\r\n<\/tbody>\r\n<\/table>\r\n<\/td>\r\n<\/tr>\r\n<\/tbody>\r\n<\/table>\r\n<table class=\"container\" role=\"presentation\" aria-hidden=\"true\" cellspacing=\"0\" cellpadding=\"12\" border=\"0\" align=\"center\" width=\"750\">\r\n<tbody>\r\n<tr>\r\n<td><\/td>\r\n<\/tr>\r\n<\/tbody>\r\n<\/table>\r\n<table class=\"container\" role=\"presentation\" cellspacing=\"0\" cellpadding=\"6\" border=\"0\" align=\"center\" width=\"750\">\r\n<tbody>\r\n<tr>\r\n<td>Denne mail er sendt fra <a href=\"https:\/\/booking.aarhus.dk\">Book Aarhus.<\/a> <\/td>\r\n<\/tr>\r\n<\/tbody>\r\n<\/table>\r\n<\/td>\r\n<\/tr>\r\n<\/tbody>\r\n<\/table>\r\n<\/td>\r\n<\/tr>\r\n<\/tbody>\r\n<\/table>\r\n<\/body>\r\n<\/html>\r\n',
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

    public static function getUserBookings1(): array
    {
        return [
            "value" => [
                [
                    "searchTerms" => ["uid-1234567890-uid"],
                    "hitsContainers" => [
                        [
                            "hits" => [
                                [
                                    "hitId" => "123",
                                ]
                            ],
                            "total" => 1,
                            "moreResultsAvailable" => false,
                        ],
                    ],
                ],
            ],
        ];
    }
}
