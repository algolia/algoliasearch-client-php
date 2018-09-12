<?php

namespace Algolia\AlgoliaSearch\Tests\Unit;

use Algolia\AlgoliaSearch\Support\ClientConfig;
use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;
use Psr\Log\NullLogger;

class ClientConfigTest extends TestCase
{
    private $defaultLogger;

    protected function setUp()
    {
        parent::setUp();

        $this->defaultLogger = $this->defaultLogger ?: ClientConfig::getDefaultLogger();

        ClientConfig::setDefaultLogger($this->defaultLogger);
    }

    public function testLogger()
    {
        $this->assertInstanceOf("Algolia\AlgoliaSearch\Log\Logger", ClientConfig::getDefaultLogger());

        $logger = new NullLogger();

        ClientConfig::setDefaultLogger($logger);

        $this->assertSame(ClientConfig::getDefaultLogger(), $logger);

        ClientConfig::setDefaultLogger($this->defaultLogger);

        $config = ClientConfig::create();

        $this->assertSame(ClientConfig::getDefaultLogger(), $config->getLogger());

        $logger = new NullLogger();

        $config->setLogger($logger);

        $this->assertSame($logger, $config->getLogger());
    }
}


