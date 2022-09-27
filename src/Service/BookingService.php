<?php

namespace App\Service;

use DateTime;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Twig\Environment;
use App\Utils\ValidationUtils;

class BookingService implements BookingServiceInterface {

  public function __construct(
    private Environment $twig,
    private ValidationUtils $validationUtils,
  ) {
  }

  /**
   * @param $submissionKeys
   * @param $data
   * @param $email
   * @param $resource
   * @return array
   * @throws \Exception
   */
  public function composeBookingContents($submissionKeys, $data, $email, $resource): array
  {
    $body = [];

    if (null == $resource) {
      throw new UnrecoverableMessageHandlingException("Resource $email not found.", 404);
    }

    $body['resource'] = $resource;
    $body['submission'] = $data;
    $body['submission']['fromObj'] = new DateTime($data['start']);
    $body['submission']['toObj'] = new DateTime($data['end']);

    return $body;
  }

  public function renderContentsAsHtml($body): string
  {
    return $this->twig->render('booking.html.twig', $body);
  }
}