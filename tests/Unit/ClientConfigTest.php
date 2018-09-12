<?php

namespace Algolia\AlgoliaSearch\Tests\Unit;

use Algolia\AlgoliaSearch\Support\ClientConfig;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class ClientConfigTest extends TestCase
{
    public function testDefaultLogger()
    {
        $this->assertInstanceOf("Algolia\AlgoliaSearch\Log\Logger", ClientConfig::getDefaultLogger());

        $logger = new NullLogger();

        ClientConfig::setDefaultLogger($logger);

        $this->assertSame(ClientConfig::getDefaultLogger(), $logger);
    }
}
