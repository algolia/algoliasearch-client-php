<?php

namespace Algolia\AlgoliaSearch\Tests\Integration;

use Algolia\AlgoliaSearch\Response\MultiResponse;
use Algolia\AlgoliaSearch\SearchClient;
use Algolia\AlgoliaSearch\Support\Helpers;
use Faker\Factory;

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
        /** @var \Algolia\AlgoliaSearch\SearchIndex $index */
        $index = SearchClient::get()->initIndex(static::$indexes['main']);

        /* adding a object with object id */
        $obj1 = $this->createStubRecord(null);
        $responses[] = $index->saveObject($obj1);

        /* adding a object w/o object id s */
        $obj2 = $this->createStubRecord(false);
        $responses[] = $index->saveObject($obj2, array('autoGenerateObjectIDIfNotExist' => true));
        
        /* adding two objects with object id  */
        $obj3 = $this->createStubRecord(null);
        $obj4 = $this->createStubRecord(null);
        $responses[] = $index->saveObjects(array($obj3, $obj4));

        /* adding two objects w/o object id  */

        $obj5 = $this->createStubRecord(false);
        $obj6 = $this->createStubRecord(false);
        $responses[] = $index->saveObjects(array($obj5, $obj6), array('autoGenerateObjectIDIfNotExist' => true));

        /* adding 1000 objects with object id with 10 batch */

        for ($i = 1; $i <= 1000; $i++) {
            $objects[$i] = $this->createStubRecord($i);
        }

        $objectsChunks = array_chunk($objects, 100);
        foreach ($objectsChunks as $chunk) {
            $request = Helpers::buildBatch($chunk, 'addObject');
            $responses[] = $index->batch($request);
        }

        /* Wait all collected task to terminate */
        $multiResponse = new MultiResponse($responses);
        $multiResponse->wait();

        /* Check 6 first records with getObject */

        $objectID1 = $responses[0][0]['objectIDs'][0];
        $objectID2 = $responses[1][0]['objectIDs'][0];
        $objectID3 = $responses[2][0]['objectIDs'][0];
        $objectID4 = $responses[2][0]['objectIDs'][1];
        $objectID5 = $responses[3][0]['objectIDs'][0];
        $objectID6 = $responses[3][0]['objectIDs'][1];

        self::assertEquals($obj1['name'], $index->getObject($objectID1)['name']);
        self::assertEquals($obj2['name'], $index->getObject($objectID2)['name']);
        self::assertEquals($obj3['name'], $index->getObject($objectID3)['name']);
        self::assertEquals($obj4['name'], $index->getObject($objectID4)['name']);
        self::assertEquals($obj5['name'], $index->getObject($objectID5)['name']);
        self::assertEquals($obj6['name'], $index->getObject($objectID6)['name']);

        /* Check 1000 remaining records with getObjects */
        $results = array($index->getObjects(array_keys($objects)));

        $objectsKeyByDefault = array();
        foreach ($objects as $object) {
            $objectsKeyByDefault[] = $object;
        }
        self::assertEquals($objectsKeyByDefault, $results[0]['results']);

        /*  Browse all records with browseObjects */
        $iterator = $index->browseObjects();

        self::assertCount(1006, $iterator);
        foreach ($iterator as $object) {
            self::assertArrayHasKey('objectID', $object);
        }

        /* Alter 1 record with partialUpdateObject */
        $obj1['name'] = 'This is an altered name';
        $responses[] = $index->partialUpdateObject($obj1);

        /* Alter 2 records with partialUpdateObjects */
        $obj3['bar'] = 'This is an altered name';
        $obj4['foo'] = 'This is an altered name';
        $responses[] = $index->partialUpdateObjects(array($obj3, $obj4));

        /* Wait all collected task to terminate */
        $multiResponse = new MultiResponse($responses);
        $multiResponse->wait();

        /* Check previous altered records with getObject */
        self::assertEquals($index->getObject($objectID1), $obj1);
        self::assertEquals($index->getObject($objectID3), $obj3);
        self::assertEquals($index->getObject($objectID4), $obj4);

        /*  Delete the first record with deleteObject */
        $responses[] = $index->deleteObject($objectID1);

        /* Delete the 5 remaining first records with deleteObjects */
        $objectsIDs = array($objectID1, $objectID2, $objectID3, $objectID4, $objectID5, $objectID6);

        $responses[] = $index->deleteObjects($objectsIDs);

        /* Delete the 1000 remaining records with clearObjects */
        $responses[] = $index->clearObjects();

        /* Wait all collected task to terminate */
        $multiResponse = new MultiResponse($responses);
        $multiResponse->wait();

        /* Browse all objects with browseObjects */
        $iterator = $index->browseObjects();
        self::assertCount(0, $iterator);
    }

    private function createStubRecord($objectID = false)
    {
        $faker = Factory::create();
        $record = array('name' => $faker->name);

        if (null === $objectID) {
            $record['objectID'] = uniqid('php_client_', true);
        } elseif (false !== $objectID) {
            $record['objectID'] = $objectID;
        }

        return $record;
    }
}
