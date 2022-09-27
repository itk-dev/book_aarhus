<?php

namespace App\Service;

interface BookingServiceInterface
{
  public function composeBookingContents($submissionKeys, $data, $email, $resource): array;
  public function renderContentsAsHtml($body): string;
}
