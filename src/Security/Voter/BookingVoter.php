<?php

namespace App\Security\Voter;

use App\Entity\Main\Booking;
use App\Repository\Main\AAKResourceRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @see https://symfony.com/doc/current/security/voters.html
 */
class BookingVoter extends Voter
{
    public const CREATE = 'BOOKING_CREATE';
    public const PERMISSION_CITIZEN = 'citizen';
    public const PERMISSION_BUSINESS_PARTNER = 'businessPartner';

    public function __construct(private AAKResourceRepository $aakResourceRepository)
    {
    }

    protected function supports(string $attribute, $subject): bool
    {
        return self::CREATE == $attribute
            && $subject instanceof Booking;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        if (!($subject instanceof Booking)) {
            return false;
        }

        $userId = $subject->getUserId();
        $userPermission = $subject->getUserPermission();

        if (empty($userId)) {
            return false;
        }

        if (!in_array($userPermission, [self::PERMISSION_CITIZEN, self::PERMISSION_BUSINESS_PARTNER])) {
            return false;
        }

        $resourceEmail = $subject->getResourceEmail();

        $resource = $this->aakResourceRepository->findOneByEmail($resourceEmail);

        if (is_null($resource)) {
            return false;
        }

        if (self::PERMISSION_CITIZEN === $userPermission && $resource->getPermissionCitizen()) {
            return true;
        }

        if (self::PERMISSION_BUSINESS_PARTNER === $userPermission && $resource->getPermissionBusinessPartner()) {
            return true;
        }

        return false;
    }
}
