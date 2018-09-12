<?php

namespace Algolia\AlgoliaSearch\Tests\Unit;

use Algolia\AlgoliaSearch\Support\ClientConfig;
use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;
use Psr\Log\NullLogger;

class ClientConfigTest extends TestCase
{
    public function testLogger()
    {
        $config = ClientConfig::create();

        $this->assertInstanceOf("Algolia\AlgoliaSearch\Log\Logger", $config->getLogger());

        $loggerA = new NullLogger();

        ClientConfig::setDefaultLogger($loggerA);

        $this->assertSame($loggerA, $config->getLogger());

        $loggerB = new NullLogger();

        $config->setLogger($loggerB);

        $this->assertSame($loggerB, $config->getLogger());
    }
}

