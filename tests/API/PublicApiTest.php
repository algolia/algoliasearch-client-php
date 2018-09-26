<?php

namespace Algolia\AlgoliaSearch\Tests\API;

use Algolia\AlgoliaSearch\Client;
use Algolia\AlgoliaSearch\Http\HttpClientFactory;
use Algolia\AlgoliaSearch\Config\ClientConfig;
use Algolia\AlgoliaSearch\Tests\RequestHttpClient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

class PublicApiTest extends TestCase
{
    public static function setUpBeforeClass()
    {
        $actualHttp = HttpClientFactory::get(new ClientConfig());

        HttpClientFactory::set(function () use ($actualHttp) {
            return new RequestHttpClient($actualHttp);
        });
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
        HttpClientFactory::reset();
    }

    public function testClient()
    {
        $client = Client::get();
        $definition = $this->getDefinition('client.yaml');

        $c = new PublicApiChecker($client, $definition);
        $c->check();
    }

    public function testIndex()
    {
        $index = Client::get()->initIndex('someindex');
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
