<?php

namespace App\Service;

use App\Entity\Main\UserBooking;
use App\Enum\UserBookingStatusEnum;
use App\Enum\UserBookingTypeEnum;
use App\Exception\BookingCreateConflictException;
use App\Exception\MicrosoftGraphCommunicationException;
use App\Exception\UserBookingException;
use DateTime;
use Exception;

/**
 * @see https://github.com/microsoftgraph/msgraph-sdk-php
 * @see https://docs.microsoft.com/en-us/graph/use-the-api
 */
class MicrosoftGraphBookingService implements BookingServiceInterface
{
    // see https://docs.microsoft.com/en-us/graph/api/resources/datetimetimezone?view=graph-rest-1.0
    // example 2019-03-15T09:00:00
    public const DATE_FORMAT = 'Y-m-d\TH:i:s';

    public function __construct(
        private readonly string $serviceAccountUsername,
        private readonly string $serviceAccountName,
        private readonly MicrosoftGraphHelperService $graphHelperService,
    ) {
    }

    /**
     * {@inheritdoc}
     *
     * @see https://docs.microsoft.com/en-us/graph/api/calendar-getschedule?view=graph-rest-1.0&tabs=http
     */
    public function getBusyIntervals(array $schedules, DateTime $startTime, DateTime $endTime, string $accessToken = null): array
    {
        // Use service account if accessToken is not set.
        $token = $accessToken ?: $this->graphHelperService->authenticateAsServiceAccount();

        $body = [
            'schedules' => $schedules,
            'startTime' => [
                'dateTime' => $startTime->setTimezone(new \DateTimeZone('UTC'))->format(MicrosoftGraphBookingService::DATE_FORMAT),
                'timeZone' => 'UTC',
            ],
            'endTime' => [
                'dateTime' => $endTime->setTimezone(new \DateTimeZone('UTC'))->format(MicrosoftGraphBookingService::DATE_FORMAT),
                'timeZone' => 'UTC',
            ],
        ];

        $response = $this->graphHelperService->request('/me/calendar/getSchedule', $token, 'POST', $body);

        $data = $response->getBody();

        $scheduleData = $data['value'];

        $result = [];

        foreach ($scheduleData as $schedule) {
            $scheduleResult = [];

            foreach ($schedule['scheduleItems'] as $scheduleItem) {
                $scheduleResult[] = [
                    'startTime' => $scheduleItem['start'],
                    'endTime' => $scheduleItem['end'],
                ];
            }

            $result[$schedule['scheduleId']] = $scheduleResult;
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     *
     * @see https://docs.microsoft.com/en-us/graph/api/user-post-events?view=graph-rest-1.0&tabs=http#examples
     *
     * @throws BookingCreateConflictException
     */
    public function createBookingForResource(string $resourceEmail, string $resourceName, string $subject, string $body, DateTime $startTime, DateTime $endTime, bool $acceptConflict = false): array
    {
        $token = $this->graphHelperService->authenticateAsServiceAccount();

        if (!$acceptConflict) {
            $bookingConflict = $this->graphHelperService->isBookingConflict($resourceEmail, $startTime, $endTime, $token);

            if ($bookingConflict) {
                throw new BookingCreateConflictException('Booking interval conflict.', 409);
            }
        }

        $body = [
            'subject' => $subject,
            'body' => [
                'contentType' => 'HTML',
                'content' => $body,
            ],
            'end' => [
                'dateTime' => $endTime->setTimezone(new \DateTimeZone('UTC'))->format(MicrosoftGraphBookingService::DATE_FORMAT),
                'timeZone' => 'UTC',
            ],
            'start' => [
                'dateTime' => $startTime->setTimezone(new \DateTimeZone('UTC'))->format(MicrosoftGraphBookingService::DATE_FORMAT),
                'timeZone' => 'UTC',
            ],
            'allowNewTimeProposals' => false,
            'showAs' => 'busy',
            'isOrganizer' => false,
            'location' => [
                'displayName' => $resourceName,
                'locationEmailAddress' => $resourceEmail,
            ],
            'attendees' => [
                [
                    'emailAddress' => [
                        'address' => $this->serviceAccountUsername,
                        'name' => $this->serviceAccountName,
                    ],
                    'type' => 'optional',
                ],
            ],
        ];

        $response = $this->graphHelperService->request("/users/$resourceEmail/events", $token, 'POST', $body);

        $status = (int) $response->getStatus();

        if (201 !== $status) {
            throw new MicrosoftGraphCommunicationException('Booking create was unsuccessful.', $status);
        }

        $content = $response->getBody();
        $iCalUId = $content['iCalUId'];

        if (!$acceptConflict) {
            if ($this->graphHelperService->isBookingConflict($resourceEmail, $startTime, $endTime, $token, [$iCalUId])) {
                $bookingId = $content['id'];

                // If another booking has been created at the same time, remove this booking.
                $response = $this->graphHelperService->request("/users/$resourceEmail/events/$bookingId", $token, 'DELETE');

                if (204 != $response->getStatus()) {
                    throw new BookingCreateConflictException('Booking interval conflict. Booking could not be removed after conflict.', 409);
                }

                throw new BookingCreateConflictException('Booking interval conflict.', 409);
            }
        }

        return $content;
    }

    /**
     * {@inheritdoc}
     *
     * @see https://docs.microsoft.com/en-us/graph/api/user-post-events?view=graph-rest-1.0&tabs=http#examples
     */
    public function createBookingInviteResource(string $resourceEmail, string $resourceName, string $subject, string $body, DateTime $startTime, DateTime $endTime): array
    {
        $token = $this->graphHelperService->authenticateAsServiceAccount();

        $body = [
            'subject' => $subject,
            'body' => [
                'contentType' => 'HTML',
                'content' => $body,
            ],
            'end' => [
                'dateTime' => $endTime->setTimezone(new \DateTimeZone('UTC'))->format(MicrosoftGraphBookingService::DATE_FORMAT),
                'timeZone' => 'UTC',
            ],
            'start' => [
                'dateTime' => $startTime->setTimezone(new \DateTimeZone('UTC'))->format(MicrosoftGraphBookingService::DATE_FORMAT),
                'timeZone' => 'UTC',
            ],
            'allowNewTimeProposals' => false,
            'showAs' => 'busy',
            'location' => [
                'displayName' => $resourceName,
                'locationEmailAddress' => $resourceEmail,
            ],
            'attendees' => [
                [
                    'emailAddress' => [
                        'address' => $resourceEmail,
                        'name' => $resourceName,
                    ],
                    'type' => 'resource',
                ],
            ],
        ];

        $response = $this->graphHelperService->request('/me/events', $token, 'POST', $body);

        $status = (int) $response->getStatus();

        if (201 !== $status) {
            throw new MicrosoftGraphCommunicationException('Booking create was unsuccessful.', $status);
        }

        return $response->getBody();
    }

    /**
     * {@inheritdoc}
     *
     * @see https://docs.microsoft.com/en-us/graph/api/event-update?view=graph-rest-1.0&tabs=http
     */
    public function updateBooking(UserBooking $booking): ?string
    {
        if ($booking->expired) {
            throw new UserBookingException('Booking is expired. Cannot be updated.', 400);
        }

        $token = $this->graphHelperService->authenticateAsServiceAccount();

        // Only allow changing start and end times.
        $newData = [
            'start' => [
                'dateTime' => $booking->start->setTimezone(new \DateTimeZone('UTC'))->format(MicrosoftGraphBookingService::DATE_FORMAT),
                'timeZone' => 'UTC',
            ],
            'end' => [
                'dateTime' => $booking->end->setTimezone(new \DateTimeZone('UTC'))->format(MicrosoftGraphBookingService::DATE_FORMAT),
                'timeZone' => 'UTC',
            ],
        ];

        $resourceMail = $booking->resourceMail;

        $bookingConflict = $this->graphHelperService->isBookingConflict($resourceMail, $booking->start, $booking->end, $token, [$booking->iCalUId]);

        if ($bookingConflict) {
            throw new UserBookingException('Booking interval conflict.', 409);
        }

        try {
            if ($booking->ownedByServiceAccount) {
                // TODO: Test that booking change results in a new message for acceptance in resource calendar.
                $bookingId = $booking->id;

                $response = $this->graphHelperService->request("/me/events/$bookingId", $token, 'PATCH', $newData);
            } else {
                $eventInResource = $this->getEventFromResourceByICalUid($resourceMail, $booking->iCalUId);

                if (is_null($eventInResource)) {
                    throw new UserBookingException('Could not find booking in resource.');
                }

                $bookingId = urlencode($eventInResource['id']);

                $response = $this->graphHelperService->request("/users/$resourceMail/events/$bookingId", $token, 'PATCH', $newData);
            }

            if (200 != $response->getStatus()) {
                throw new UserBookingException('Booking could not be updated', (int) $response->getStatus());
            }

            return $response->getStatus();
        } catch (Exception $e) {
            throw new UserBookingException($e->getMessage(), (int) $e->getCode());
        }
    }

    /**
     * {@inheritdoc}
     *
     * @see https://docs.microsoft.com/en-us/graph/api/event-delete?view=graph-rest-1.0&tabs=http
     */
    public function deleteBooking(UserBooking $booking)
    {
        if ($booking->expired) {
            throw new UserBookingException('Booking is expired. Cannot be deleted.', 400);
        }

        if ($booking->ownedByServiceAccount) {
            $this->deleteBookingFromResource($booking);
            $this->deleteBookingFromServiceAccount($booking);
        } else {
            $this->deleteBookingFromServiceAccount($booking);
            $this->deleteBookingFromResource($booking);
        }
    }

    /**
     * @throws MicrosoftGraphCommunicationException
     * @throws UserBookingException
     */
    private function deleteBookingFromServiceAccount(UserBooking $booking): void
    {
        $token = $this->graphHelperService->authenticateAsServiceAccount();

        $bookingId = $booking->id;

        // Remove from service account.
        $response = $this->graphHelperService->request("/me/events/$bookingId", $token, 'DELETE');

        if (204 != $response->getStatus()) {
            throw new UserBookingException('Booking could not be removed', (int) $response->getStatus());
        }
    }

    /**
     * @throws MicrosoftGraphCommunicationException
     * @throws UserBookingException
     */
    private function deleteBookingFromResource(UserBooking $booking): void
    {
        $token = $this->graphHelperService->authenticateAsServiceAccount();

        $eventInResource = $this->getEventFromResourceByICalUid($booking->resourceMail, $booking->iCalUId);

        if (is_null($eventInResource)) {
            throw new UserBookingException('Booking not found in resource', 404);
        }

        $bookingId = urlencode($eventInResource['id']);
        $userId = $booking->resourceMail;

        // Remove from resource.
        $response = $this->graphHelperService->request("/users/$userId/events/$bookingId", $token, 'DELETE');

        if (204 != $response->getStatus()) {
            throw new UserBookingException('Booking could not be removed from resource', (int) $response->getStatus());
        }
    }

    /**
     * {@inheritdoc}
     *
     * @see https://docs.microsoft.com/en-us/graph/search-concept-events
     */
    public function getBooking(string $bookingId): array
    {
        $token = $this->graphHelperService->authenticateAsServiceAccount();

        // TODO: Move this out of the service an in to the code receiving the request.
        // Formatting the url decoded booking id, replacing "/" with "-" as this is graph-compatible, and replacing
        // " " with "+", as some encoding issue between javascript and php replaces "+" with " ".
        $cleanedBookingId = str_replace(['/', ' '], ['-', '+'], urldecode($bookingId));

        $response = $this->graphHelperService->request('/me/events/'.$cleanedBookingId, $token);

        return $response->getBody();
    }

    /**
     * {@inheritdoc}
     *
     * @see https://docs.microsoft.com/en-us/graph/search-concept-events
     */
    public function getUserBookings(string $userId): array
    {
        try {
            $now = new DateTime();
            $page = 0;
            $pageSize = 5;

            $userBookings = [];

            do {
                $data = $this->getUserBookingsPage($userId, $page, $pageSize);

                $userBookings = array_merge($userBookings, $data['userBookings']);

                $last = $userBookings[count($userBookings) - 1];

                if ($last->end < $now) {
                    break;
                }

                $page = $page + 1;
            } while ($data['moreResultsAvailable']);

            return $userBookings;
        } catch (Exception $e) {
            throw new MicrosoftGraphCommunicationException($e->getMessage(), (int) $e->getCode());
        }
    }

    /**
     * @throws MicrosoftGraphCommunicationException
     */
    private function getUserBookingsPage(string $userId, int $page = 0, int $pageSize = 25): array
    {
        try {
            $token = $this->graphHelperService->authenticateAsServiceAccount();

            $body = [
                'requests' => [
                    [
                        'entityTypes' => ['event'],
                        'query' => [
                            'queryString' => $this->createBodyUserId($userId),
                        ],
                        'from' => $page * $pageSize,
                        'size' => $pageSize,
                    ],
                ],
            ];

            $response = $this->graphHelperService->request('/search/query', $token, 'POST', $body);

            $resultBody = $response->getBody();

            $result = $resultBody['value'][0]['hitsContainers'][0] ?? [];
            $hits = $result['hits'] ?? [];

            $responseData = [
                'userBookings' => [],
                'total' => $result['total'] ?? null,
                'moreResultsAvailable' => $result['moreResultsAvailable'] ?? false,
                'page' => $page,
                'pageSize' => $pageSize,
            ];

            if (!empty($result) && !empty($hits)) {
                foreach ($hits as $hit) {
                    $id = urlencode($hit['hitId']);

                    $userBookingGraphData = $this->getBooking($id);

                    $responseData['userBookings'][] = $this->getUserBookingFromApiData($userBookingGraphData);
                }
            }

            return $responseData;
        } catch (Exception $e) {
            throw new MicrosoftGraphCommunicationException($e->getMessage(), (int) $e->getCode());
        }
    }

    /**
     * {@inheritdoc}
     *
     * @see https://learn.microsoft.com/en-us/graph/api/resources/event?view=graph-rest-1.0
     * @see https://learn.microsoft.com/en-us/graph/api/resources/responsestatus?view=graph-rest-1.0
     */
    public function getUserBookingFromApiData(array $data): UserBooking
    {
        try {
            // Formatting the url decoded booking id, replacing "/" with "-" as this is graph-compatible, and replacing
            // " " with "+", as some encoding issue between javascript and php replaces "+" with " ".
            $cleanedBookingId = str_replace(['/', ' '], ['-', '+'], $data['id']);

            $userBooking = new UserBooking();
            $userBooking->id = $cleanedBookingId;
            $userBooking->start = new \DateTime($data['start']['dateTime'], new \DateTimeZone($data['start']['timeZone']));
            $userBooking->end = new \DateTime($data['end']['dateTime'], new \DateTimeZone($data['end']['timeZone']));
            $userBooking->iCalUId = $data['iCalUId'];
            $userBooking->subject = $data['subject'] ?? '';
            $userBooking->displayName = $data['location']['displayName'];
            $userBooking->body = $data['body']['content'];

            $locationUniqueId = $data['location']['uniqueId'];
            $organizerEmail = $data['organizer']['emailAddress']['address'] ?? null;

            $userBooking->ownedByServiceAccount = $organizerEmail && mb_strtolower($organizerEmail) == mb_strtolower($this->serviceAccountUsername);

            $bookingType = $userBooking->ownedByServiceAccount ? UserBookingTypeEnum::ACCEPTANCE : UserBookingTypeEnum::INSTANT;
            $userBooking->bookingType = $bookingType->name;

            // Find resource mail.
            $attendeeResource = null;

            foreach ($data['attendees'] as $attendee) {
                if (mb_strtolower($attendee['emailAddress']['name']) == mb_strtolower($locationUniqueId)) {
                    $attendeeResource = $attendee;
                    break;
                }
            }

            if (is_null($attendeeResource)) {
                throw new Exception('Could not find location in attendee list', 400);
            }

            $userBooking->resourceMail = $attendeeResource['emailAddress']['address'];
            $userBooking->resourceName = $attendeeResource['emailAddress']['name'];

            $status = UserBookingStatusEnum::NONE;
            $attendeeResourceStatus = $attendeeResource['status']['response'] ?? null;
            $responseStatus = $data['responseStatus']['response'] ?? null;
            $statusResponse = $userBooking->ownedByServiceAccount ? $attendeeResourceStatus : $responseStatus;

            switch ($statusResponse) {
                case 'accepted':
                    $status = UserBookingStatusEnum::ACCEPTED;
                    break;
                case 'declined':
                    $status = UserBookingStatusEnum::DECLINED;
                    break;
                case 'none':
                    if ($userBooking->ownedByServiceAccount) {
                        $status = UserBookingStatusEnum::AWAITING_APPROVAL;
                    }
            }

            $userBooking->status = $status->name;

            $userBooking->expired = $userBooking->end < new DateTime();

            return $userBooking;
        } catch (Exception $exception) {
            throw new UserBookingException($exception->getMessage(), (int) $exception->getCode());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function createBodyUserId(string $id): string
    {
        return "UID-$id-UID";
    }

    /**
     * @throws MicrosoftGraphCommunicationException
     * @throws UserBookingException
     *
     * @see https://learn.microsoft.com/en-us/graph/api/user-list-events?view=graph-rest-1.0&tabs=http
     * @see https://learn.microsoft.com/en-us/graph/query-parameters
     */
    private function getEventFromResourceByICalUid(string $resourceEmail, string $iCalUId): ?array
    {
        $token = $this->graphHelperService->authenticateAsServiceAccount();

        $path = "/users/$resourceEmail/events?\$filter=iCalUId eq '$iCalUId'";

        $r = $this->graphHelperService->request($path, $token);

        $body = $r->getBody();

        if (isset($body['value'])) {
            $numberOfResults = count($body['value']);

            if (1 == $numberOfResults) {
                return array_pop($body['value']);
            } elseif ($numberOfResults > 1) {
                throw new UserBookingException('More than one event found with iCalUId', 500);
            }
        }

        return null;
    }
}
