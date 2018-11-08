<?php

namespace Algolia\AlgoliaSearch\Tests\Integration;

use Algolia\AlgoliaSearch\SearchClient;

class IndexingTest extends AlgoliaIntegrationTestCase
{
    protected function setUp()
    {
        parent::setUp();
        static::$indexes['main'] = self::safeName('indexing');
    }

    public function testIndexing()
    {
        $responses = array();
        $objectIDs = array();
        /** @var \Algolia\AlgoliaSearch\SearchIndex $index */
        $index = SearchClient::get()->initIndex(static::$indexes['main']);

        $responses[] = $index->saveObject($this->createStubRecord($objectIDs[] = 'first'));
        $tmp = $index->saveObject($this->createStubRecord(false), array('autoGenerateObjectIDIfNotExist' => true));
        $responses[] = $tmp;
        $objectIDs = array_merge($objectIDs, $tmp[0]['objectIDs']);

        $responses[] = $index->saveObjects(array(
            $this->createStubRecord($objectIDs[] = 'second'), $this->createStubRecord($objectIDs[] = 'third'),
        ));
        $tmp = $index->saveObjects(array(
            $this->createStubRecord(false), $this->createStubRecord(false),
        ), array('autoGenerateObjectIDIfNotExist' => true));
        $responses[] = $tmp;
        $objectIDs = array_merge($objectIDs, $tmp[0]['objectIDs']);

        $batch = array();
        for ($i = 1; $i <= 1000; $i++) {
            $batch[] = $this->createStubRecord($i);
            if (0 === $i % 100) {
                $responses[] = $index->saveObjects($batch);
                $batch = array();
            }
        }

        foreach ($responses as $r) {
            $r->wait();
        }

        foreach ($objectIDs as $id) {
            $this->assertArraySubset($this->createStubRecord($id), $index->getObject($id));
        }

        $objectIDs = array_merge($objectIDs, range(1, 1000));
        $count = 0;
        foreach ($index->browseObjects() as $object) {
            $this->assertContains($object['objectID'], $objectIDs);
            unset($objectIDs['objectID']);
            $count++;
        }
        $this->assertEquals(1006, $count);
        $this->assertCount($count, $objectIDs);
    }

    private function createStubRecord($objectID = false)
    {
        $record = array('content' => 'something');

        if (null === $objectID) {
            $record['objectID'] = uniqid('php_client_', true);
        } elseif (false !== $objectID) {
            $record['objectID'] = $objectID;
        }

        return $record;
    }
}
