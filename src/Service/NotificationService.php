<?php

namespace App\Service;

use App\Entity\Main\Booking;
use App\Entity\Main\UserBooking;
use App\Entity\Resources\AAKResource;
use App\Enum\NotificationTypeEnum;
use App\Exception\BuildNotificationException;
use App\Exception\NoNotificationReceiverException;
use App\Exception\UnsupportedNotificationTypeException;
use App\Utils\ValidationUtils;
use DateTimeZone as PhpDateTimeZone;
use Eluceo\iCal\Domain\Entity;
use Eluceo\iCal\Domain\Entity\TimeZone;
use Eluceo\iCal\Domain\ValueObject\DateTime as ICalDateTime;
use Eluceo\iCal\Domain\ValueObject\GeographicPosition;
use Eluceo\iCal\Domain\ValueObject\Location;
use Eluceo\iCal\Domain\ValueObject\TimeSpan;
use Eluceo\iCal\Presentation\Component;
use Eluceo\iCal\Presentation\Factory;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\MimeTypes;

class NotificationService implements NotificationServiceInterface
{
    private ?string $validatedAdminNotificationEmail;

    public function __construct(
        private readonly string $emailFromAddress,
        private readonly string $emailAdminNotification,
        private readonly ValidationUtils $validationUtils,
        private readonly LoggerInterface $logger,
        private readonly MailerInterface $mailer,
        private readonly string $bindNotificationTimezone,
        private readonly string $bindNotificationDateFormat,
        private readonly Metric $metric,
    ) {
        try {
            $this->validatedAdminNotificationEmail = $this->validationUtils->validateEmail(
                $this->emailAdminNotification
            );
        } catch (\Exception) {
            $this->logger->warning('No admin notification email set.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function sendBookingNotification(Booking $booking, ?AAKResource $resource, NotificationTypeEnum $type): void
    {
        $this->metric->incFunctionTotal($this, __FUNCTION__, Metric::INVOKE);

        $data = [
            'booking' => $booking,
            'resource' => $resource,
            'user' => [
                'name' => $booking->getUserName(),
                'mail' => $booking->getUserMail(),
            ],
            'metaData' => $booking->getMetaData(),
        ];

        $notification = $this->buildNotification($type, $data);

        $this->sendNotification($notification);
    }

    /**
     * {@inheritdoc}
     */
    public function sendUserBookingNotification(
        UserBooking $userBooking,
        ?AAKResource $resource,
        NotificationTypeEnum $type
    ): void {
        $this->metric->incFunctionTotal($this, __FUNCTION__, Metric::INVOKE);

        $body = $userBooking->body;

        $crawler = new Crawler($body);

        $node = $crawler->filterXPath('//*[@id="email"]')->getNode(0);

        if (is_null($node)) {
            throw new NoNotificationReceiverException('Cannot send user booking notification. No user email in body.');
        }

        $email = $node->nodeValue;

        $node = $crawler->filterXPath('//*[@id="name"]')->getNode(0);

        if (is_null($node)) {
            throw new NoNotificationReceiverException('Cannot send user booking notification. No user name in body.');
        }

        $name = $node->nodeValue;

        $dateStart = $userBooking->start;
        $dateEnd = $userBooking->end;

        $dateStart->setTimezone(new \DateTimeZone($this->bindNotificationTimezone));
        $dateEnd->setTimezone(new \DateTimeZone($this->bindNotificationTimezone));

        $resourceName = $resource?->getResourceDisplayName() ?? $userBooking->displayName;

        $notificationData = [
            'from' => $this->emailFromAddress,
            'to' => $email,
            'subject' => null,
            'template' => 'email-booking-changed.html.twig',
            'adminNotification' => false,
            'data' => [
                'user' => [
                    'name' => $name,
                    'email' => $email,
                ],
                'booking' => [
                    'subject' => $userBooking->subject,
                    'startTime' => $userBooking->start,
                    'endTime' => $userBooking->end,
                ],
                'resource' => [
                    'resourceName' => $resourceName,
                ],
                'startFormatted' => $dateStart->format($this->bindNotificationDateFormat),
                'endFormatted' => $dateEnd->format($this->bindNotificationDateFormat),
            ],
            'fileAttachments' => [],
        ];

        switch ($type) {
            case NotificationTypeEnum::DELETE_SUCCESS:
                $notificationData['subject'] = 'Din booking er blevet slettet.';
                $notificationData['data']['subject'] = 'Din booking er blevet slettet.';
                $notificationData['template'] = 'email-booking-deleted.html.twig';
                $notifyResourceSubject = 'Følgende booking blev slettet';

                break;
            case NotificationTypeEnum::UPDATE_SUCCESS:
                $notificationData['subject'] = 'Din booking er blevet opdateret.';
                $notificationData['data']['subject'] = 'Din booking er blevet opdateret.';
                $notifyResourceSubject = 'Følgende booking blev ændret';

                break;
            default:
                throw new UnsupportedNotificationTypeException(sprintf('Error sending UserBooking notification: Unsupported NotificationTypeEnum "%s"', $type->name));
        }

        $this->sendNotification($notificationData);

        // Email notification to resource as well.

        $notificationData['subject'] = $notifyResourceSubject;
        $notificationData['to'] = $userBooking->resourceMail;
        $notificationData['adminNotification'] = true;

        $this->sendNotification($notificationData);
    }

    /**
     * {@inheritdoc}
     */
    public function createCalendarComponent(array $eventData): Component
    {
        $location = new Location($eventData['location_name']);

        if ($eventData['coordinates']) {
            $coordinatesArr = explode(',', $eventData['coordinates']);
            $location = $location->withGeographicPosition(
                new GeographicPosition(
                    (float) $coordinatesArr['0'],
                    (float) $coordinatesArr['1']
                )
            );
        }

        $event = new Entity\Event();

        $event->setSummary($eventData['summary']);
        $event->setDescription($eventData['description']);
        $event->setLocation($location);

        $dateFrom = $eventData['start']->setTimezone(new \DateTimeZone($this->bindNotificationTimezone));
        $dateTo = $eventData['end']->setTimezone(new \DateTimeZone($this->bindNotificationTimezone));

        $start = new ICalDateTime($dateFrom, true);
        $end = new ICalDateTime($dateTo, true);

        $occurrence = new TimeSpan($start, $end);
        $event->setOccurrence($occurrence);

        $calendar = new Entity\Calendar([$event]);

        $phpDateTimeZone = new PhpDateTimeZone($this->bindNotificationTimezone);
        $timeZone = TimeZone::createFromPhpDateTimeZone(
            $phpDateTimeZone,
            $dateFrom,
            $dateTo
        );
        $calendar->addTimeZone($timeZone);

        return (new Factory\CalendarFactory())->createCalendar($calendar);
    }

    /**
     * {@inheritdoc}
     */
    public function notifyAdmin(string $subject, string $message, ?Booking $booking, ?AAKResource $resource): void
    {
        $this->metric->incFunctionTotal($this, __FUNCTION__, Metric::INVOKE);

        if ($this->validatedAdminNotificationEmail) {
            $to = $this->validatedAdminNotificationEmail;
            $template = 'email-notify-admin.html.twig';

            if (null !== $booking) {
                $dateStart = $booking->getStartTime();
                $dateEnd = $booking->getEndTime();

                $dateStart->setTimezone(new \DateTimeZone($this->bindNotificationTimezone));
                $dateEnd->setTimezone(new \DateTimeZone($this->bindNotificationTimezone));

                $dateStartString = $dateStart->format($this->bindNotificationDateFormat);
                $dateEndString = $dateEnd->format($this->bindNotificationDateFormat);
            }

            $data = [
                'subject' => $subject,
                'message' => $message,
                'booking' => $booking,
                'resource' => $resource,
                'startFormatted' => $dateStartString ?? '',
                'endFormatted' => $dateEndString ?? '',
            ];

            $notificationData = [
                'from' => $this->emailFromAddress,
                'to' => $to,
                'subject' => $subject,
                'template' => $template,
                'data' => $data,
            ];

            $this->sendNotification($notificationData);
        }
    }

    /**
     * @param NotificationTypeEnum $type
     * @param array $data
     *
     * @return array
     *
     * @throws BuildNotificationException
     */
    private function buildNotification(NotificationTypeEnum $type, array $data): array
    {
        $fileAttachments = [];
        $to = $data['user']['mail'];
        /** @var AAKResource $resource */
        $resource = $data['resource'];
        $resourceName = $resource->getResourceDisplayName() ?? $resource->getResourceName();
        $location = $resource->getLocation();
        $resourceLocationString = $resourceName.' - '.$location;

        switch ($type) {
            case NotificationTypeEnum::SUCCESS:
                $template = 'email-booking-success.html.twig';
                $subject = 'Booking bekræftelse: '.$resourceLocationString;

                $event = $this->prepareICalEvent($data);
                $iCalendarComponent = $this->createCalendarComponent($event);

                $fileAttachments = [
                    'ics' => [$iCalendarComponent],
                ];
                break;
            case NotificationTypeEnum::REQUEST_RECEIVED:
                $template = 'email-booking-request-received-receipt.html.twig';
                $subject = 'Booking anmodning modtaget: '.$resourceLocationString;
                break;
            case NotificationTypeEnum::FAILED:
                $template = 'email-booking-failed.html.twig';
                $subject = 'Booking lykkedes ikke: '.$resourceLocationString;
                break;
            case NotificationTypeEnum::CONFLICT:
                $template = 'email-booking-failed.html.twig';
                $subject = 'Booking lykkedes ikke. Intervallet er optaget: '.$resourceLocationString;
                break;
            default:
                throw new BuildNotificationException('Unsupported NotificationTypeEnum');
        }

        if (isset($data['booking'])) {
            /** @var Booking $booking */
            $booking = $data['booking'];

            $dateStart = $booking->getStartTime();
            $dateEnd = $booking->getEndTime();

            $dateStart->setTimezone(new \DateTimeZone($this->bindNotificationTimezone));
            $dateEnd->setTimezone(new \DateTimeZone($this->bindNotificationTimezone));

            $data['startFormatted'] = $dateStart->format($this->bindNotificationDateFormat);
            $data['endFormatted'] = $dateEnd->format($this->bindNotificationDateFormat);
        }

        return [
            'from' => $this->emailFromAddress,
            'to' => $to,
            'subject' => $subject,
            'template' => $template,
            'data' => $data,
            'fileAttachments' => $fileAttachments,
        ];
    }

    /**
     * @param array $notification
     *
     * @return void
     *
     * @throws TransportExceptionInterface
     */
    private function sendNotification(array $notification): void
    {
        $email = (new TemplatedEmail())
            ->from($notification['from'])
            ->to(new Address($notification['to']))
            ->subject($notification['subject'])
            ->htmlTemplate($notification['template'])
            ->context($notification);

        // Add ics attachment to mail.
        if (!empty($notification['fileAttachments']['ics'])) {
            $tempDir = sys_get_temp_dir();
            $bookingId = $notification['data']['booking']->getId();
            $fileName = 'booking-'.$bookingId.'.ics';
            $filePath = $tempDir.'/'.$fileName;

            foreach ($notification['fileAttachments']['ics'] as $key => $ics) {
                try {
                    file_put_contents($filePath, (string) $ics);

                    $mimeTypes = new MimeTypes();
                    $mimeType = $mimeTypes->guessMimeType($filePath);

                    $email->attach(fopen($filePath, 'r'), 'booking-'.$key.'.ics', $mimeType);
                } finally {
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                }
            }
        }

        $this->mailer->send($email);
    }

    /**
     * @param array $data
     *
     * @return array
     */
    private function prepareICalEvent(array $data): array
    {
        return [
            'summary' => $data['booking']->getSubject(),
            'start' => $data['booking']->getStartTime(),
            'end' => $data['booking']->getEndTime(),
            'description' => $data['booking']->getSubject(),
            'coordinates' => $data['resource']->getGeoCoordinates(),
            'location_name' => $data['resource']->getLocation().' - '.$data['resource']->getResourceName(),
        ];
    }
}
