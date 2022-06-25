<?php

namespace App\Dto;

final class WebformBookingInput
{
    public array $data = [
        'webform' => [
            'id' => '',
        ],
        'submission' => [
            'uuid' => '',
        ],
    ];

    public array $links = [
        'sender' => '',
        'get_submission_url' => '',
    ];
}
