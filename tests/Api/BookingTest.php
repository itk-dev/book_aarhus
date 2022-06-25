<?php

namespace App\Tests\Api;

use App\Message\CreateBookingMessage;
use App\Message\WebformSubmitMessage;
use App\Tests\AbstractBaseApiTestCase;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

class BookingTest extends AbstractBaseApiTestCase
{
    use InteractsWithMessenger;

    public function testBookingWebform(): void
    {
        $this->messenger('async')->queue()->assertEmpty();

        $client = $this->getAuthenticatedClient();

        $requestData = [
            'data' => [
                'webform' => [
                    'id' => 'booking',
                ],
                'submission' => [
                    'uuid' => '795f5a1c-a0ac-4f8a-8834-bb71fca8585d',
                ],
            ],
            'links' => [
                'sender' => 'https://bookaarhus.local.itkdev.dk',
                'get_submission_url' => 'https://bookaarhus.local.itkdev.dk/webform_rest/booking/submission/123123123',
            ],
        ];

        $client->request('POST', '/v1/bookings-webform', [
            'json' => $requestData,
        ]);

        $this->assertResponseStatusCodeSame(201);

        $this->messenger('async')->queue()->assertCount(1);
        $this->messenger('async')->queue()->assertContains(WebformSubmitMessage::class);

        /** @var WebformSubmitMessage $message */
        $message = $this->messenger('async')->queue()->first(WebformSubmitMessage::class)->getMessage();
        $this->assertEquals('booking', $message->getWebformId());
        $this->assertEquals('795f5a1c-a0ac-4f8a-8834-bb71fca8585d', $message->getSubmissionUuid());
        $this->assertEquals('https://bookaarhus.local.itkdev.dk', $message->getSender());
        $this->assertEquals('https://bookaarhus.local.itkdev.dk/webform_rest/booking/submission/123123123', $message->getSubmissionUrl());
        $this->assertEquals(1, $message->getApiKeyUserId());
    }

    public function testInvalidBookingWebform(): void
    {
        $this->messenger('async')->queue()->assertEmpty();

        $client = $this->getAuthenticatedClient();

        $requestData = [
            'data' => [
                'webform' => [
                    'id' => 'booking',
                ],
                'submission' => [
                    'uuid' => '795f5a1c-a0ac-4f8a-8834-bb71fca8585d',
                ],
            ],
        ];

        $client->request('POST', '/v1/bookings-webform', [
            'json' => $requestData,
        ]);

        $this->assertResponseStatusCodeSame(400);

        $this->messenger('async')->queue()->assertCount(0);
    }

    public function testBooking(): void
    {
        $this->messenger('async')->queue()->assertEmpty();

        $client = $this->getAuthenticatedClient();

        $requestData = [
            'resourceEmail' => 'test@example.com',
            'resourceName' => 'Test',
            'subject' => 'Subject',
            'body' => 'Body',
            'startTime' => '2022-06-25T10:00:00.000Z',
            'endTime' => '2022-06-25T10:30:00.000Z',
        ];

        $client->request('POST', '/v1/bookings', [
            'json' => $requestData,
        ]);

        $this->assertResponseStatusCodeSame(201);

        $this->messenger('async')->queue()->assertCount(1);
        $this->messenger('async')->queue()->assertContains(CreateBookingMessage::class);

        /** @var CreateBookingMessage $message */
        $message = $this->messenger('async')->queue()->first(CreateBookingMessage::class)->getMessage();
        $booking = $message->getBooking();
        $this->assertEquals('test@example.com', $booking->getResourceEmail());
        $this->assertEquals('Test', $booking->getResourceName());
        $this->assertEquals('Subject', $booking->getSubject());
        $this->assertEquals('Body', $booking->getBody());
        $this->assertEquals('2022-06-25T10:00:00+00:00', $booking->getStartTime()->format('c'));
        $this->assertEquals('2022-06-25T10:30:00+00:00', $booking->getEndTime()->format('c'));
    }

    public function testInvalidBooking(): void
    {
        $this->messenger('async')->queue()->assertEmpty();

        $client = $this->getAuthenticatedClient();

        $requestData = [
            'resourceEmail' => 'test',
            'resourceName' => 'Test',
            'subject' => 'Subject',
            'body' => 'Body',
            'startTime' => '2022-06-25T10:00:00.000Z',
            'endTime' => '2022-06-25T10:30:00.000Z',
        ];

        $client->request('POST', '/v1/bookings', [
            'json' => $requestData,
        ]);

        $this->assertResponseStatusCodeSame(400);

        $this->messenger('async')->queue()->assertCount(0);

        $requestData = [
            'resourceEmail' => 'test@example.com',
            'resourceName' => 'Test',
            'subject' => 'Subject',
            'body' => 'Body',
            'startTime' => 'not a date',
            'endTime' => '2022-06-25T10:30:00.000Z',
        ];

        $client->request('POST', '/v1/bookings', [
            'json' => $requestData,
        ]);

        $this->assertResponseStatusCodeSame(400);

        $this->messenger('async')->queue()->assertCount(0);
    }
}
