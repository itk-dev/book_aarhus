<?php

namespace App\Security\Voter;

use App\Entity\Main\UserBooking;
use App\Service\BookingServiceInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @see https://symfony.com/doc/current/security/voters.html
 *
 * @extends Voter<string, mixed>
 */
class UserBookingVoter extends Voter
{
    public const VIEW = 'USER_BOOKING_VIEW';
    public const EDIT = 'USER_BOOKING_EDIT';
    public const DELETE = 'USER_BOOKING_DELETE';

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly BookingServiceInterface $bookingService,
    ) {
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

        $crawler = new Crawler($body);

        $node = $crawler->filterXPath('//*[@id="userId"]')->getNode(0);

        if (is_null($node)) {
            return false;
        }

        $userIdInBooking = $node->nodeValue;

        if ($this->bookingService->createBodyUserId($userId) == $userIdInBooking) {
            return true;
        }

        return false;
    }
}
