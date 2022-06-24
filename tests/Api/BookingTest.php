<?php

namespace App\Tests\Api;

use App\Tests\AbstractBaseApiTestCase;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

class BookingTest extends AbstractBaseApiTestCase
{
    use InteractsWithMessenger;

    public function testQueue(): void
    {
        // assert against the queue
        $this->messenger('async')->queue()->assertEmpty();
    }
}
