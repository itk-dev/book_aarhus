<?php

namespace App\Utils;

use ApiPlatform\Metadata\Exception\InvalidArgumentException;

interface ValidationUtilsInterface
{
    /**
     * Validate date from string.
     *
     * @param string $dateString
     * @param string|null $format
     *
     * @return \DateTime
     *
     * @throws \Exception
     */
    public function validateDate(string $dateString, ?string $format = null): \DateTime;

    /**
     * @param string $email
     *
     * @return string
     *
     * @throws InvalidArgumentException
     */
    public function validateEmail(string $email): string;
}
