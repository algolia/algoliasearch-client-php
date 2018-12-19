<?php

namespace Algolia\AlgoliaSearch\Tests\Integration;

class MultipleIndexTest extends AlgoliaIntegrationTestCase
{
    public function testMultipleIndexMethods()
    {
        /** @var \Algolia\AlgoliaSearch\SearchClient $client */
        $client = self::getClient();
        $batch = $this->getBatch();

        $client->multipleBatch($batch);
        $result = $client->multipleQueries(array(), array('strategy' => 'stopIfEnoughMatches'));
        $this->assertArraySubset(array('results' => array()), $result);
        $result = $client->multipleQueries(array(
            array('indexName' => static::$indexes['europe']),
            array('indexName' => static::$indexes['america']),
        ));
        $this->assertGreaterThan(0, count($result['results'][0]['hits']));
        $this->assertGreaterThan(0, count($result['results'][1]['hits']));

        $retrieved = $client->multipleGetObjects($this->getMultipleGetBatch());
        $this->assertEquals(count($batch), count($retrieved['results']));

        $client->multipleBatch($this->getDeleteBatch());
        foreach (static::$indexes as $indexName) {
            $res = $client->initIndex($indexName)->search('');
            $this->assertEquals(0, $res['nbHits']);
        }
    }

    private function getBatch()
    {
        $batch = array();
        $actions = array('addObject', 'updateObject', 'partialUpdateObject');

        foreach ($this->airports as $airport) {
            static::$indexes[$airport['zone']] = self::safeName($airport['zone']);

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
