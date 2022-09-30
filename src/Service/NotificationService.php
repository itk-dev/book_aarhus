<?php

namespace App\Service;

use JetBrains\PhpStorm\ArrayShape;
use JsonException;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class NotificationService implements NotificationServiceInterface
{
    public function __construct(private MailerInterface $mailer)
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
    #[ArrayShape(['from' => 'string', 'to' => 'string', 'subject' => 'string', 'template' => 'null|string', 'data' => ''])]
  private function buildNotification($type, $data): array
  {
      $template = null;
      // @todo Set from email.
      $from = 'my@aarhus.dk';
      $to = $data['webformSubmission']['submissiondata']['email'];
      $subject = 'Booking bekræftigelse: '.$data['resource']->getResourceName().' - '.$data['resource']->getLocation();
      switch ($type) {
          case 'success':
              $template = 'emailBookingSuccess.html.twig';
              break;
          case 'booking_changed':
              $template = 'emailBookingChanged.html.twig';
              $subject = 'Booking ændret: '.$data['resource']->getResourceName().' - '.$data['resource']->getLocation();
              break;
          case 'booing_failed':
              $template = 'emailBookingFailed.html.twig';
              $subject = 'Booking lykkedes ikke: '.$data['resource']->getResourceName().' - '.$data['resource']->getLocation();
              break;
      }

      return [
      'from' => $from,
      'to' => $to,
      'subject' => $subject,
      'template' => $template,
      'data' => $data,
    ];
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

            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
        }
    }
}
