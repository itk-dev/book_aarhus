<?php

namespace App\Utils;

use ApiPlatform\Core\Exception\InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ValidationUtils
{
    public function __construct(private ValidatorInterface $validator, private string $bindDefaultDateFormat)
    {
    }

    public function validateDate(string $date, string $format = null): \DateTime
    {
        $format = $format ?? $this->bindDefaultDateFormat;

        $errors = $this->validator->validate($date, new Assert\DateTime($format));
        if (0 !== count($errors)) {
            $epoc = \DateTime::createFromFormat($format, '1970-01-01T01:02:03.000Z');
            $example = $epoc->format($this->bindDefaultDateFormat);
            throw new InvalidArgumentException(sprintf('%s is not a valid date format, valid format is simplified extended ISO format, e.g. %s', $date, $example));
        }

        return new \DateTime($date);
    }

    public function validateEmail(string $email): string
    {
        $errors = $this->validator->validate($email, new Assert\Email());

        if (0 !== count($errors)) {
            throw new InvalidArgumentException("$email is not a valid email format, valid format is e.g. test@bookaarhus.local.itkdev.dk");
        }

        return $email;
    }
}
