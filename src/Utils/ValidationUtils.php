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

    public function validateDate(string $date): \DateTime
    {
        $errors = $this->validator->validate($date, new Assert\DateTime($this->bindDefaultDateFormat));
        if (0 !== count($errors)) {
            $epoc = \DateTime::createFromFormat('Y-m-d\TH:i:s.v\Z', '1970-01-01T01:02:03.000Z');
            $example = $epoc->format($this->bindDefaultDateFormat);
            throw new InvalidArgumentException(sprintf('%s is not a valid date format, valid format is simplified extended ISO format, e.g %s', $date, $example));
        }

        return new \DateTime($date);
    }
}
