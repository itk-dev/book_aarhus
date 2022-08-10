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

    public function validateDate(string $dateString, string $format = null): \DateTime
    {
        $format = $format ?? $this->bindDefaultDateFormat;

        $errors = $this->validator->validate($dateString, new Assert\DateTime($format));
        if (0 !== count($errors)) {
            $epoc = new \DateTime();
            $example = $epoc->format($format);
            throw new InvalidArgumentException(sprintf('%s is not a valid date format, valid format is e.g. %s', $dateString, $example));
        }

        return new \DateTime($dateString);
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
