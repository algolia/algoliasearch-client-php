<?php

namespace Algolia\AlgoliaSearch\Tests\Unit;

class ResponseObjectTest extends NullTestCase
{
    public function testResponseObjectIsArrayAccessible()
    {
        $response = static::$client->addApiKey(array('search'));
        $this->assertInstanceOf('Algolia\AlgoliaSearch\Response\AddApiKeyResponse', $response);
        $this->assertTrue(method_exists($response, 'wait'));

        $response = static::$client->updateApiKey('the-key', array('acl' => 'analytics', 'editSettings'));
        $this->assertInstanceOf('Algolia\AlgoliaSearch\Response\UpdateApiKeyResponse', $response);
        $this->assertTrue(method_exists($response, 'wait'));

        $response = static::$client->deleteApiKey('key');
        $this->assertInstanceOf('Algolia\AlgoliaSearch\Response\DeleteApiKeyResponse', $response);
        $this->assertTrue(method_exists($response, 'wait'));

        $response = static::$client->multipleBatch(array());
        $this->assertInstanceOf('Algolia\AlgoliaSearch\Response\MultipleIndexBatchIndexingResponse', $response);
        $this->assertTrue(method_exists($response, 'wait'));
    }

    public function testIndexingResponse()
    {
        $i = static::$client->initIndex('cool');

        $this->assertInstanceOfResponse($i->moveTo('new-name'));
        $this->assertInstanceOfResponse($i->copyTo('new-name'));

        $this->assertInstanceOfResponse($i->setSettings(array('objectID' => 'test')));
        $this->assertInstanceOfResponse($i->copySettingsTo('indexName'));

        $this->assertInstanceOfResponse($i->saveObject(array('objectID' => 'test')));
        $this->assertInstanceOfResponse($i->saveObjects(array(array('objectID' => 'test'))));
        $this->assertInstanceOfResponse($i->partialUpdateObject(array('objectID' => 'test')));
        $this->assertInstanceOfResponse($i->partialUpdateObjects(array(array('objectID' => 'test'))));
        $this->assertInstanceOfResponse($i->replaceAllObjects(array(array('objectID' => 'test'))));
        $this->assertInstanceOfResponse($i->deleteObject(array('objectID' => 'test')));
        $this->assertInstanceOfResponse($i->deleteObjects(array('objectID' => 'test')));
        $this->assertInstanceOfResponse($i->deleteBy(array('objectID' => 'test')));
        $this->assertInstanceOfResponse($i->clearObjects());
        $this->assertInstanceOfResponse($i->batch(array('objectID' => 'test')));

        $this->assertInstanceOfResponse($i->saveSynonym(array('objectID' => 'test')));
        $this->assertInstanceOfResponse($i->saveSynonyms(array('objectID' => 'test')));
        $this->assertInstanceOfResponse($i->replaceAllSynonyms(array('objectID' => 'test')));
        $this->assertInstanceOfResponse($i->copySynonymsTo('indexName'));
        $this->assertInstanceOfResponse($i->deleteSynonym('objectID'));
        $this->assertInstanceOfResponse($i->clearSynonyms(array('objectID' => 'test')));

        $this->assertInstanceOfResponse($i->saveRule(array('objectID' => 'test')));
        $this->assertInstanceOfResponse($i->saveRules(array('objectID' => 'test')));
        $this->assertInstanceOfResponse($i->replaceAllRules(array('objectID' => 'test')));
        $this->assertInstanceOfResponse($i->copyRulesTo('indexName'));
        $this->assertInstanceOfResponse($i->deleteRule('objectID'));
        $this->assertInstanceOfResponse($i->clearRules(array('objectID' => 'test')));
    }

    private function assertInstanceOfResponse($response)
    {
        if (is_array($response)) {
            foreach ($response as $r) {
                $this->assertInstanceOfResponse($r);
            }

            return;
        }

        $this->assertInstanceOf('Algolia\AlgoliaSearch\Response\AbstractResponse', $response);
        $this->assertTrue(method_exists($response, 'wait'));
    }
}
