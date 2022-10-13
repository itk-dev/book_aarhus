<?php

namespace App\Security\Voter;

use App\Entity\Main\UserBooking;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @see https://symfony.com/doc/current/security/voters.html
 */
class UserBookingVoter extends Voter
{
    public const VIEW = 'USER_BOOKING_VIEW';
    public const EDIT = 'USER_BOOKING_EDIT';
    public const DELETE = 'USER_BOOKING_DELETE';

    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE])
            && $subject instanceof UserBooking;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        if (!($subject instanceof UserBooking)) {
            return false;
        }

        $request = $this->requestStack->getCurrentRequest();

        if (is_null($request)) {
            return false;
        }

        $userId = $request->headers->get('Authorization-UserId') ?? null;

        if (is_null($userId)) {
            return false;
        }

        $body = $subject->body;

        return str_contains($body, "USERID-$userId-USERID");
    }
}
