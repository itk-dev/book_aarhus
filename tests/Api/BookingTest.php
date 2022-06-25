<?php

namespace App\Tests\Api;

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

        $this->messenger('async')->reset();
        $this->messenger('async')->queue()->assertCount(0);
    }
}
