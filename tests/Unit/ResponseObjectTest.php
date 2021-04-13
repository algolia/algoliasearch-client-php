<?php

namespace Algolia\AlgoliaSearch\Tests\Unit;

class ResponseObjectTest extends NullTestCase
{
    public function testResponseObjectIsArrayAccessible()
    {
        $response = static::$client->addApiKey(['search']);
        $this->assertInstanceOf('Algolia\AlgoliaSearch\Response\AddApiKeyResponse', $response);
        $this->assertTrue(method_exists($response, 'wait'));

        $response = static::$client->updateApiKey('the-key', ['acl' => 'analytics', 'editSettings']);
        $this->assertInstanceOf('Algolia\AlgoliaSearch\Response\UpdateApiKeyResponse', $response);
        $this->assertTrue(method_exists($response, 'wait'));

        $response = static::$client->deleteApiKey('key');
        $this->assertInstanceOf('Algolia\AlgoliaSearch\Response\DeleteApiKeyResponse', $response);
        $this->assertTrue(method_exists($response, 'wait'));

        $response = static::$client->multipleBatch([]);
        $this->assertInstanceOf('Algolia\AlgoliaSearch\Response\MultipleIndexBatchIndexingResponse', $response);
        $this->assertTrue(method_exists($response, 'wait'));
    }

    public function testIndexingResponse()
    {
        $c = static::$client;
        $i = $c->initIndex('cool');

        $this->assertInstanceOfResponse($c->moveIndex('old-name', 'new-name'));
        $this->assertInstanceOfResponse($c->copyIndex('old-name', 'new-name'));

        $this->assertInstanceOfResponse($i->setSettings(['objectID' => 'test']));
        $this->assertInstanceOfResponse($c->copySettings('old-name', 'new-name'));

        $this->assertInstanceOfResponse($i->saveObject(['objectID' => 'test']));
        $this->assertInstanceOfResponse($i->saveObjects([['objectID' => 'test']]));
        $this->assertInstanceOfResponse($i->partialUpdateObject(['objectID' => 'test']));
        $this->assertInstanceOfResponse($i->partialUpdateObjects([['objectID' => 'test']]));
        $this->assertInstanceOfResponse($i->replaceAllObjects([['objectID' => 'test']]));
        $this->assertInstanceOfResponse($i->deleteObject(['objectID' => 'test']));
        $this->assertInstanceOfResponse($i->deleteObjects(['objectID' => 'test']));
        $this->assertInstanceOfResponse($i->deleteBy(['objectID' => 'test']));
        $this->assertInstanceOfResponse($i->clearObjects());
        $this->assertInstanceOfResponse($i->batch(['objectID' => 'test']));

        $this->assertInstanceOfResponse($i->saveSynonym(['objectID' => 'test']));
        $this->assertInstanceOfResponse($i->saveSynonyms(['objectID' => 'test']));
        $this->assertInstanceOfResponse($i->replaceAllSynonyms(['objectID' => 'test']));
        $this->assertInstanceOfResponse($c->copySynonyms('old-name', 'new-name'));
        $this->assertInstanceOfResponse($i->deleteSynonym('objectID'));
        $this->assertInstanceOfResponse($i->clearSynonyms(['objectID' => 'test']));

        $this->assertInstanceOfResponse($i->saveRule(['objectID' => 'test']));
        $this->assertInstanceOfResponse($i->saveRules([['objectID' => 'test']]));
        $this->assertInstanceOfResponse($i->replaceAllRules([['objectID' => 'test']]));
        $this->assertInstanceOfResponse($c->copyRules('old-name', 'new-name'));
        $this->assertInstanceOfResponse($i->deleteRule('objectID'));
        $this->assertInstanceOfResponse($i->clearRules(['objectID' => 'test']));
    }

    public function testNullResponseForEmptyDataset()
    {
        $c = static::$client;
        $i = $c->initIndex('cool');

        $this->assertInstanceOfResponse($i->saveObjects([]), 'Algolia\AlgoliaSearch\Response\NullResponse');
        $this->assertInstanceOfResponse($i->saveSynonyms([]), 'Algolia\AlgoliaSearch\Response\NullResponse');
        $this->assertInstanceOfResponse($i->saveRules([]), 'Algolia\AlgoliaSearch\Response\NullResponse');
    }

    private function assertInstanceOfResponse($response, $class = '')
    {
        if (!$class) {
            $class = 'Algolia\AlgoliaSearch\Response\AbstractResponse';
        }

        if (is_array($response)) {
            foreach ($response as $r) {
                $this->assertInstanceOfResponse($r);
            }

            return;
        }

        $this->assertInstanceOf($class, $response);
        $this->assertTrue(method_exists($response, 'wait'));
    }
}
