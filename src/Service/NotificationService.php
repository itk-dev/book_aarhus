<?php

namespace App\Service;

use DateTimeImmutable;
use Eluceo\iCal\Domain\ValueObject\DateTime;
use Eluceo\iCal\Domain\ValueObject\TimeSpan;
use Eluceo\iCal\Presentation\Component;
use Symfony\Component\Mime\MimeTypes;
use Exception;
use JetBrains\PhpStorm\ArrayShape;
use JsonException;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Eluceo\iCal\Domain\Entity;
use Eluceo\iCal\Presentation\Factory;

class NotificationService implements NotificationServiceInterface
{
  public function __construct(private string $emailFromAddress, private MailerInterface $mailer)
  {
  }

  /**
   * @param $booking
   * @param $resource
   * @param string $type
   */
  public function sendBookingNotification($booking, $resource, string $type)
  {
    try {
      $webformSubmission = json_decode($booking->getWebformSubmission(), associative: true, flags: JSON_THROW_ON_ERROR);

      $data = [
        'booking' => $booking,
        'resource' => $resource,
        'webformSubmission' => $webformSubmission,
      ];

      $notification = $this->buildNotification($type, $data);

      $this->sendNotification($notification);

    } catch (JsonException $e) {
    }
  }

  /**
   * @param $type
   * @param $data
   *
   * @return array
   */
  #[ArrayShape(['from' => "string", 'to' => "mixed", 'subject' => "string", 'template' => "null|string", 'data' => "", 'fileAttachments' => "array"])]
  private function buildNotification($type, $data): array
  {
    try {
      $template = NULL;
      $fileAttachments = [];
      // @todo Set from email.
      $to = $data['webformSubmission']['submissionData']['email'];
      $subject = 'Booking bekræftigelse: ' . $data['resource']->getResourceName() . ' - ' . $data['resource']->getLocation();
      switch ($type) {
        case 'success':
          $template = 'emailBookingSuccess.html.twig';

          $events = $this->prepareIcalEvents($data);
          $iCalendarComponent = $this->createCalendarComponent($events);

          $fileAttachments = [
            'ics' => [$iCalendarComponent]
          ];
          break;
        case 'booking_changed':
          $template = 'emailBookingChanged.html.twig';
          $subject = 'Booking ændret: ' . $data['resource']->getResourceName() . ' - ' . $data['resource']->getLocation();
          break;
        case 'booking_failed':
          $template = 'emailBookingFailed.html.twig';
          $subject = 'Booking lykkedes ikke: ' . $data['resource']->getResourceName() . ' - ' . $data['resource']->getLocation();
          break;
      }

      return [
        'from' => $this->emailFromAddress,
        'to' => $to,
        'subject' => $subject,
        'template' => $template,
        'data' => $data,
        'fileAttachments' => $fileAttachments,
      ];
    } catch (Exception $e) {
    }
  }

  /**
   * @param $notification
   */
  private function sendNotification($notification)
  {
    try {
      $email = (new TemplatedEmail())
        ->from($notification['from'])
        ->to(new Address($notification['to']))
        ->subject($notification['subject'])
        ->htmlTemplate($notification['template'])
        ->context($notification);

      // Add ics attachment to mail.
      if ($notification['fileAttachments']['ics']) {
        foreach ($notification['fileAttachments']['ics'] as $ics) {
          try {
            $tempDir = sys_get_temp_dir();
            $bookingId = $notification['data']['booking']->getId();
            $fileName = 'booking-' . $bookingId . '.ics';
            $filePath = $tempDir . '/' . $fileName;

            file_put_contents($filePath, (string)$ics);

            $mimeTypes = new MimeTypes();
            $mimeType = $mimeTypes->guessMimeType($filePath);

            $email->attach(fopen($filePath, 'r'), $fileName, $mimeType);
          }
          finally {
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
   * @param $data
   * @return \array[][]
   */
  private function prepareIcalEvents($data): array
  {
    return [
      [
        'summary' => $data['booking']->getSubject(),
        'description' => $data['booking']->getBody(),
        'from' => $data['booking']->getStartTime()->format('Y-m-d H:i:s'),
        'to' => $data['booking']->getEndTime()->format('Y-m-d H:i:s'),
      ]
    ];
  }

  /**
   * @param $events
   * @return Component
   */
  public function createCalendarComponent($events): Component
  {
    $iCalEvents = [];

    foreach ($events as $eventData) {
      $event = new Entity\Event();

      $event->setSummary($eventData['summary']);
      $event->setDescription($eventData['description']);

      $start = new DateTime(DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $eventData['from']), false);
      $end = new DateTime(DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $eventData['to']), false);
      $occurrence = new TimeSpan($start, $end);
      $event->setOccurrence($occurrence);

      $iCalEvents[] = $event;
    }

    $calendar = new Entity\Calendar($iCalEvents);
    return (new Factory\CalendarFactory())->createCalendar($calendar);
  }
}
