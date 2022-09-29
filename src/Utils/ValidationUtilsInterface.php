<?php

namespace App\Utils;

interface ValidationUtilsInterface
{
  public function validateDate(string $dateString, string $format = null): \DateTime;

  public function validateEmail(string $email): string;
}