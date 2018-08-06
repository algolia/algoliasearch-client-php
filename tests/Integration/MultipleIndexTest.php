<?php

namespace Algolia\AlgoliaSearch\Tests\Integration;

use Algolia\AlgoliaSearch\Exceptions\NotFoundException;

class MultipleIndexTest extends AlgoliaIntegrationTestCase
{
    public function testMultipleIndexMethods()
    {
        /** @var \Algolia\AlgoliaSearch\Client $client */
        $client = $this->getClient();
        $batch =  $this->getBatch();

        $client->multipleBatchObjects($batch);

        $result = $client->multipleQueries(array(), array('strategy' => 'stopIfEnoughMatches'));
        $this->assertArraySubset(array('results' => array()), $result);
        $result = $client->multipleQueries(array(
            array('indexName' => 'europe'),
            array('indexName' => 'america'),
        ));
        $this->assertGreaterThan(0, count($result['results'][0]['hits']));
        $this->assertGreaterThan(0, count($result['results'][1]['hits']));

        $retrieved = $client->multipleGetObjects($this->getMultipleGetBatch());
        $this->assertEquals(count($batch), count($retrieved['results']));

        $client->multipleBatchObjects($this->getDeleteBatch());
        foreach (static::$indexes as $indexName) {
            try {
                $res = $client->initIndex($indexName)->search('');
                $this->assertTrue(false);
            } catch (NotFoundException $e) {
                $this->assertTrue(true);
            }
        }
    }

    /**
     * @expectedException \Algolia\AlgoliaSearch\Exceptions\MissingObjectId
     */
    public function testObjectIdIsRequired()
    {
        $batch = array_map(function ($item) {
            unset($item['body']['objectID']);
            return $item;
        }, $this->getBatch());

        $this->getClient()->multipleBatchObjects($batch);
    }

    private function getBatch()
    {
        $batch = array();
        $actions = array("addObject", "updateObject", "partialUpdateObject");

        foreach ($this->airports as $airport) {
            static::$indexes[$airport['zone']] = $this->safeName($airport['zone']);

            $batch[] = array(
                'action' => $actions[rand(0, 2)],
                'indexName' => static::$indexes[$airport['zone']],
                'body' => $airport,
            );
        }

        return $batch;
    }

    private function getDeleteBatch()
    {
        return array_map(function ($item) {
            $item['action'] = 'deleteObject';
            return $item;
        }, $this->getBatch());
    }

    private function getMultipleGetBatch()
    {
        return array_map(function ($item) {
            return array(
                'indexName' => $item['indexName'],
                'objectID' => $item['body']['objectID'],
            );
        }, $this->getBatch());
    }
}
