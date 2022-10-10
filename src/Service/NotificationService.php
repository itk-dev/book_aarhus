<?php

namespace App\Service;

use DateTimeImmutable;
use Eluceo\iCal\Domain\Entity;
use Eluceo\iCal\Domain\ValueObject\DateTime;
use Eluceo\iCal\Domain\ValueObject\TimeSpan;
use Eluceo\iCal\Presentation\Component;
use Eluceo\iCal\Presentation\Factory;
use Exception;
use JsonException;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\MimeTypes;

class NotificationService implements NotificationServiceInterface
{
    public function __construct(private readonly string $emailFromAddress, private readonly MailerInterface $mailer)
    {
    }

    /**
     * @param $booking
     * @param $resource
     * @param string $type
     *
     * @return void
     */
    public function sendBookingNotification($booking, $resource, string $type): void
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
        }
    }

    /**
     * @param string $type
     * @param array $data
     *
     * @return array
     */
    private function buildNotification(string $type, array $data): array
    {
        $notificationData = [];
        try {
            $template = null;
            $fileAttachments = [];
            $to = $data['user']['mail'];
            $subject = 'Booking bekræftigelse: '.$data['resource']->getResourceName().' - '.$data['resource']->getLocation();
            switch ($type) {
                case 'success':
                    $template = 'emailBookingSuccess.html.twig';

                    $events = $this->prepareIcalEvents($data);
                    $iCalendarComponent = $this->createCalendarComponent($events);

                    $fileAttachments = [
                        'ics' => [$iCalendarComponent],
                    ];
                    break;
                case 'booking_changed':
                    $template = 'emailBookingChanged.html.twig';
                    $subject = 'Booking ændret: '.$data['resource']->getResourceName().' - '.$data['resource']->getLocation();
                    break;
                case 'booking_failed':
                    $template = 'emailBookingFailed.html.twig';
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
            $tempDir = sys_get_temp_dir();
            $bookingId = $notification['data']['booking']->getId();
            $fileName = 'booking-'.$bookingId.'.ics';
            $filePath = $tempDir.'/'.$fileName;
            // Add ics attachment to mail.
            if ($notification['fileAttachments']['ics']) {
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
            ],
        ];
    }

    /**
     * @param array $events
     *
     * @return Component
     *
     * @throws Exception
     */
    public function createCalendarComponent(array $events): Component
    {
        $iCalEvents = [];

        foreach ($events as $eventData) {
            $event = new Entity\Event();

            $event->setSummary($eventData['summary']);
            $event->setDescription($eventData['description']);

            $immutableFrom = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $eventData['from']);
            $immutableTo = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $eventData['from']);

            if (false === $immutableFrom || false === $immutableTo) {
                throw new Exception('DateTimeImmutable cannot be false');
            }

            $start = new DateTime($immutableFrom, false);
            $end = new DateTime($immutableTo, false);
            $occurrence = new TimeSpan($start, $end);
            $event->setOccurrence($occurrence);

            $iCalEvents[] = $event;
        }

        $calendar = new Entity\Calendar($iCalEvents);

        return (new Factory\CalendarFactory())->createCalendar($calendar);
    }
}
