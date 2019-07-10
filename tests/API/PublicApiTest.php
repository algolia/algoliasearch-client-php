<?php

namespace Algolia\AlgoliaSearch\Tests\API;

use Algolia\AlgoliaSearch\Algolia;
use Algolia\AlgoliaSearch\SearchClient;
use Algolia\AlgoliaSearch\Tests\NullHttpClient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * @internal
 */
class PublicApiTest extends TestCase
{
    public static function setUpBeforeClass()
    {
        Algolia::setHttpClient(new NullHttpClient());
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
        Algolia::resetHttpClient();
    }

    public function testClient()
    {
        $client = SearchClient::create();
        $definition = $this->getDefinition('client.yaml');

        $c = new PublicApiChecker($client, $definition);
        $c->check();
    }

    public function testIndex()
    {
        $index = SearchClient::create()->initIndex('someindex');
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
}
