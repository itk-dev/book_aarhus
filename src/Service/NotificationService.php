<?php

namespace App\Service;

use App\Entity\Main\Booking;
use App\Entity\Main\UserBooking;
use App\Entity\Resources\AAKResource;
use App\Enum\NotificationTypeEnum;
use App\Utils\ValidationUtils;
use DateTimeImmutable;
use DateTimeZone as PhpDateTimeZone;
use Eluceo\iCal\Domain\Entity;
use Eluceo\iCal\Domain\Entity\TimeZone;
use Eluceo\iCal\Domain\ValueObject\DateTime;
use Eluceo\iCal\Domain\ValueObject\GeographicPosition;
use Eluceo\iCal\Domain\ValueObject\Location;
use Eluceo\iCal\Domain\ValueObject\TimeSpan;
use Eluceo\iCal\Presentation\Component;
use Eluceo\iCal\Presentation\Factory;
use Exception;
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
        private readonly MailerInterface $mailer
    ) {
        try {
            $this->validatedAdminNotificationEmail = $this->validationUtils->validateEmail($this->emailAdminNotification);
        } catch (Exception) {
            $this->logger->warning('No admin notification email set.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function sendBookingNotification(Booking $booking, ?AAKResource $resource, NotificationTypeEnum $type): void
    {
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
    public function sendUserBookingNotification(UserBooking $userBooking, ?AAKResource $resource, NotificationTypeEnum $type): void
    {
        $body = $userBooking->body;

        $crawler = new Crawler($body);

        $node = $crawler->filterXPath('//*[@id="email"]')->getNode(0);

        if (is_null($node)) {
            $this->logger->error('Cannot send user booking notification. No user email in body.');

            return;
        }

        $email = $node->nodeValue;

        $node = $crawler->filterXPath('//*[@id="name"]')->getNode(0);

        if (is_null($node)) {
            $this->logger->error('Cannot send user booking notification. No user name in body.');

            return;
        }

        $name = $node->nodeValue;

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
                    'resourceName' => $userBooking->resourceName,
                ],
            ],
            'fileAttachments' => [],
        ];

        $notifyResourceSubject = null;

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
                $this->logger->error('Error sending UserBooking notification: Unsupported NotificationTypeEnum');
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
    public function createCalendarComponent(array $events): Component
    {
        $iCalEvents = [];
        $immutableFrom = null;
        $immutableTo = null;
        foreach ($events as $eventData) {
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

            $immutableFrom = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $eventData['from']);
            $immutableTo = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $eventData['to']);

            if (false === $immutableFrom || false === $immutableTo) {
                throw new Exception('DateTimeImmutable cannot be false');
            }

            $start = new DateTime($immutableFrom, true);
            $end = new DateTime($immutableTo, true);
            $occurrence = new TimeSpan($start, $end);
            $event->setOccurrence($occurrence);
            $event->setLocation($location);

            $iCalEvents[] = $event;
        }

        $calendar = new Entity\Calendar($iCalEvents);

        $phpDateTimeZone = new PhpDateTimeZone('Europe/Copenhagen');
        $timeZone = TimeZone::createFromPhpDateTimeZone(
            $phpDateTimeZone,
            $immutableFrom,
            $immutableTo,
        );
        $calendar->addTimeZone($timeZone);

        return (new Factory\CalendarFactory())->createCalendar($calendar);
    }

    /**
     * {@inheritdoc}
     */
    public function notifyAdmin(string $subject, string $message, ?Booking $booking, ?AAKResource $resource): void
    {
        if ($this->validatedAdminNotificationEmail) {
            $to = $this->validatedAdminNotificationEmail;
            $template = 'email-notify-admin.html.twig';

            $data = [
                'subject' => $subject,
                'message' => $message,
                'booking' => $booking,
                'resource' => $resource,
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
     */
    private function buildNotification(NotificationTypeEnum $type, array $data): array
    {
        $notificationData = [];

        try {
            $template = null;
            $subject = null;
            $fileAttachments = [];
            $to = $data['user']['mail'];

            switch ($type) {
                case NotificationTypeEnum::SUCCESS:
                    $template = 'email-booking-success.html.twig';
                    $subject = 'Booking bekræftelse: '.$data['resource']->getResourceName().' - '.$data['resource']->getLocation();

                    $events = $this->prepareIcalEvents($data);
                    $iCalendarComponent = $this->createCalendarComponent($events);

                    $fileAttachments = [
                        'ics' => [$iCalendarComponent],
                    ];
                    break;
                case NotificationTypeEnum::REQUEST_RECEIVED:
                    $template = 'email-booking-request-received-receipt.html.twig';
                    $subject = 'Booking anmodning modtaget: '.$data['resource']->getResourceName().' - '.$data['resource']->getLocation();
                    break;
                case NotificationTypeEnum::FAILED:
                    $template = 'email-booking-failed.html.twig';
                    $subject = 'Booking lykkedes ikke: '.$data['resource']->getResourceName().' - '.$data['resource']->getLocation();
                    break;
                case NotificationTypeEnum::CONFLICT:
                    $template = 'email-booking-failed.html.twig';
                    $subject = 'Booking lykkedes ikke. Intervallet er optaget: '.$data['resource']->getResourceName().' - '.$data['resource']->getLocation();
                    break;
            }

            $notificationData = [
                'from' => $this->emailFromAddress,
                'to' => $to,
                'subject' => $subject,
                'template' => $template,
                'data' => $data,
                'fileAttachments' => $fileAttachments,
            ];
        } catch (Exception $e) {
            $this->logger->error('Error building notification: '.$e->getMessage());
        }

        return $notificationData;
    }

    /**
     * @param array $notification
     *
     * @return void
     */
    private function sendNotification(array $notification): void
    {
        try {
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
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('Error sending notification: '.$e->getMessage());
        }
    }

    /**
     * @param array $data
     *
     * @return array[]
     */
    private function prepareIcalEvents(array $data): array
    {
        return [
            [
                'summary' => $data['booking']->getSubject(),
                'description' => $data['booking']->getSubject(),
                'from' => $data['booking']->getStartTime()->format('Y-m-d H:i:s'),
                'to' => $data['booking']->getEndTime()->format('Y-m-d H:i:s'),
                'coordinates' => $data['resource']->getGeoCoordinates(),
                'location_name' => $data['resource']->getLocation().' - '.$data['resource']->getResourceName(),
            ],
        ];
    }
}
