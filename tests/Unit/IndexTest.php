<?php

namespace Algolia\AlgoliaSearch\Tests\Unit;

use Algolia\AlgoliaSearch\Index;
use Algolia\AlgoliaSearch\Internals\ApiWrapper;
use Algolia\AlgoliaSearch\Support\ClientConfig;
use Algolia\AlgoliaSearch\Support\Config;
use Algolia\AlgoliaSearch\Tests\FakeBatchIterator;
use Algolia\AlgoliaSearch\Tests\GimmeTheRequestHttpClient;
use PHPUnit\Framework\TestCase;

class IndexTest extends TestCase
{
    private $index;

    public function __construct()
    {
        parent::__construct();

        $this->index = new Index(
            'ìndexÑäme',
            new ApiWrapper(
                new GimmeTheRequestHttpClient(
                    Config::getHttpClient()
                ),
                new ClientConfig('algoliaAppId', 'algoliaApiKey')
            )
        );
    }

    public function testBatchIterator()
    {
        $objects = new FakeBatchIterator();
        $response = $this->index->saveObjects($objects);

        $this->assertCount(11, $response);

        $last = end($response);
        $batch1 = (string) $last['request']->getBody();
        $batch1 = json_decode($batch1, true);
        $batch1 = $batch1['requests'];
        $this->assertArraySubset(array(
            'action' => 'addObject',
            'body' => array(
                'objectID' => 1100,
                'someAttribute' => 'text',
            ),
        ), reset($batch1));
    }
}
