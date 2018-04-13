<?php

namespace Algolia\AlgoliaSearch\Tests\API;

use Algolia\AlgoliaSearch\Client;
use Algolia\AlgoliaSearch\Internals\ApiWrapper;
use Algolia\AlgoliaSearch\Tests\TestCase;
use Symfony\Component\Yaml\Yaml;

class PublicApiTest extends TestCase
{
    public function testClient()
    {
        $apiWrapper = $this->createMock('\Algolia\AlgoliaSearch\Internals\ApiWrapper');
        $client = new Client($apiWrapper);
        $definition = $this->getDefinition();

        $c = new PublicApiChecker($client, $definition);
        $c->check();
    }

    private function getDefinition()
    {
        $definition = Yaml::parse(file_get_contents(__DIR__.'/client.yaml'));

        foreach ($definition as &$def) {
            if (!isset($def['args'])) {
                $def['args'] = array();
            }
        }

        return $definition;
    }
}
