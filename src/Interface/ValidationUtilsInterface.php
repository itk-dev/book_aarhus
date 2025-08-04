<?php

namespace App\Interface;

use ApiPlatform\Metadata\Exception\InvalidArgumentException;

interface ValidationUtilsInterface
{
    /**
     * Validate date from string.
     *
     * @throws \Exception
     */
    public function validateDate(string $dateString, ?string $format = null): \DateTime;

    /**
     * @throws InvalidArgumentException
     */
    public function validateEmail(string $email): string;
}
