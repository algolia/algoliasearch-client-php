<?php

namespace Algolia\AlgoliaSearch\Tests\API;

use Algolia\AlgoliaSearch\Client;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

class PublicApiTest extends TestCase
{
    public function testClient()
    {
        $apiWrapper = $this->createMock('\Algolia\AlgoliaSearch\Internals\ApiWrapper');
        $client = new Client($apiWrapper);
        $definition = $this->getDefinition('client.yaml');

        $c = new PublicApiChecker($client, $definition);
        $c->check();
    }

    public function testIndex()
    {
        $apiWrapper = $this->createMock('\Algolia\AlgoliaSearch\Internals\ApiWrapper');
        $client = new Client($apiWrapper);
        $index = $client->initIndex('someindex');
        $definition = $this->getDefinition('index.yaml');

        $c = new PublicApiChecker($index, $definition);
        $c->check();
    }

    private function getDefinition($filename)
    {
        $definition = Yaml::parse(file_get_contents(__DIR__.'/'.$filename));

        foreach ($definition as &$def) {
            if (!isset($def['args'])) {
                $def['args'] = array();
            }
        }

        return $definition;
    }

    protected function createMock($originalClassName)
    {
        if (method_exists($this, 'getMock')) {
            return $this->getMock($originalClassName);
        }

        return parent::createMock($originalClassName);
    }
}
