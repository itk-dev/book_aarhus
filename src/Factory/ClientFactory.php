<?php

namespace App\Factory;

use GuzzleHttp\Client;
use Microsoft\Graph\Graph;

class ClientFactory
{
    public function getGuzzleClient(): Client
    {
        return new Client();
    }

    public function getGraph(): Graph
    {
        return new Graph();
    }
}
