<?php

namespace App\Service;

use App\Entity\Main\Booking;
use App\Entity\Resources\AAKResource;
use App\Enum\NotificationTypeEnum;
use App\Utils\ValidationUtils;
use DateTimeImmutable;
use Eluceo\iCal\Domain\Entity;
use Eluceo\iCal\Domain\ValueObject\DateTime;
use Eluceo\iCal\Domain\ValueObject\GeographicPosition;
use Eluceo\iCal\Domain\ValueObject\Location;
use Eluceo\iCal\Domain\ValueObject\TimeSpan;
use Eluceo\iCal\Presentation\Component;
use Eluceo\iCal\Presentation\Factory;
use Exception;
use JsonException;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
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
        try {
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
        } catch (JsonException $e) {
            // TODO: Handle error.
        }
    }

    /**
     * {@inheritdoc}
     */
    public function createCalendarComponent(array $events): Component
    {
        $iCalEvents = [];

        foreach ($events as $eventData) {
            // Set location:
            $location = new Location($eventData['location_name']);

            if ($eventData['coordinates']) {
              $coordinatesArr = explode(',', $eventData['coordinates']);
              $location = $location->withGeographicPosition(
                new GeographicPosition(
                  (float)$coordinatesArr['0'],
                  (float)$coordinatesArr['1']
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

            $start = new DateTime($immutableFrom, false);
            $end = new DateTime($immutableTo, false);
            $occurrence = new TimeSpan($start, $end);
            $event->setOccurrence($occurrence);
            $event->setLocation($location);

            $iCalEvents[] = $event;
        }

        $calendar = new Entity\Calendar($iCalEvents);

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
            $fileAttachments = [];
            $to = $data['user']['mail'];
            $subject = 'Booking bekræftelse: '.$data['resource']->getResourceName().' - '.$data['resource']->getLocation();

            switch ($type) {
                case NotificationTypeEnum::SUCCESS:
                    $template = 'email-booking-success.html.twig';

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
                case NotificationTypeEnum::CHANGED:
                    $template = 'email-booking-changed.html.twig';
                    $subject = 'Booking ændret: '.$data['resource']->getResourceName().' - '.$data['resource']->getLocation();
                    break;
                case NotificationTypeEnum::FAILED:
                    $template = 'email-booking-failed.html.twig';
                    $subject = 'Booking lykkedes ikke: '.$data['resource']->getResourceName().' - '.$data['resource']->getLocation();
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
            // TODO: Handle error.
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
                'description' => $data['booking']->getBody(),
                'from' => $data['booking']->getStartTime()->format('Y-m-d H:i:s'),
                'to' => $data['booking']->getEndTime()->format('Y-m-d H:i:s'),
                'coordinates' => $data['resource']->getGeoCoordinates(),
                'location_name' => $data['resource']->getLocation() . ' - ' . $data['resource']->getResourceName(),
            ],
        ];
    }
}
