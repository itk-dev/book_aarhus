<?php

namespace App\Dto;

final class BookingInput
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
