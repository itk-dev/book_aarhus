<?php

namespace App\Service;

use App\Entity\Main\UserBooking;
use App\Exception\UserBookingException;
use Exception;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Uid\Ulid;

class BookingService implements BookingServiceInterface
{
    /**
     * {@inheritdoc}
     */
    public function getUserBookingFromGraphData(array $data): UserBooking
    {
        try {
            $userBooking = new UserBooking();
            $userBooking->id = urlencode($data['id']);
            $userBooking->hitId = $data['id'] ?? '';
            $userBooking->start = new \DateTime($data['start']['dateTime'], new \DateTimeZone($data['start']['timeZone'])) ?? null;
            $userBooking->end = new \DateTime($data['end']['dateTime'], new \DateTimeZone($data['end']['timeZone'])) ?? null;
            $userBooking->iCalUId = $data['iCalUId'];
            $userBooking->subject = $data['resource']['subject'] ?? '';
            $userBooking->displayName = $data['location']['displayName'];
            $userBooking->body = $data['body']['content'];

            $crawler = new Crawler($userBooking->body);

            $node = $crawler->filterXPath('//*[@id="bookingUniqueId"]')->getNode(0);

            if (is_null($node) || empty($node->nodeValue)) {
                throw new Exception('bookingUuid not set for booking', 400);
            }

            $userBooking->bookingUniqueId = $node->nodeValue;

            return $userBooking;
        } catch (Exception $exception) {
            throw new UserBookingException($exception->getMessage(), (int) $exception->getCode());
        }
    }

    public function createBodyBookingId(): string
    {
        $bookingId = sha1(Ulid::generate());

        return "BID-$bookingId-BID";
    }

    public function createBodyUserId(string $id): string
    {
        return "UID-$id-UID";
    }
}
