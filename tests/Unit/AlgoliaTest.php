<?php

namespace Algolia\AlgoliaSearch\Tests\Unit;

use Algolia\AlgoliaSearch\Algolia;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class AlgoliaTest extends TestCase
{
    public function testLogger()
    {
        $this->assertInstanceOf("Algolia\AlgoliaSearch\Log\Logger", Algolia::getLogger());

        $loggerB = new NullLogger();

        Algolia::setLogger($loggerB);

        $this->assertSame($loggerB, Algolia::getLogger());
    }
}
