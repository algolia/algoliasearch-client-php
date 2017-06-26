<?php

namespace AlgoliaSearch\Tests;

use AlgoliaSearch\AlgoliaException;
use AlgoliaSearch\Client;
use AlgoliaSearch\Index;

class RulesTest extends AlgoliaSearchTestCase
{
    /** @var Client */
    private $client;

    /** @var Index */
    private $index;

    protected function setUp()
    {
        $this->client = new Client(getenv('ALGOLIA_APPLICATION_ID'), getenv('ALGOLIA_API_KEY'));
        $this->index = $this->client->initIndex($this->safe_name('àlgol?à-php'));
        $this->index->clearIndex();
        $this->index->clearRules();
    }

    protected function tearDown()
    {
        try {
            $this->client->deleteIndex($this->safe_name('àlgol?à-php'));
        } catch (AlgoliaException $e) {
        }
    }

    public function testSaveAndGetRule()
    {
        $rule = $this->getRuleStub();

        $response = $this->index->saveRule('my-rule', $rule);
        $this->index->waitTask($response['taskID']);

        $this->assertEquals($rule, $this->index->getRule('my-rule'));
    }

    /**
     * @depends testSaveAndGetRule
     * @expectedException \AlgoliaSearch\AlgoliaException
     * @expectedExceptionMessage ObjectID does not exist
     */
    public function testDeleteRule()
    {
        $rule = $this->getRuleStub();

        $response = $this->index->saveRule('my-rule', $rule);
        $this->index->waitTask($response['taskID']);

        $response = $this->index->deleteRule('my-rule');
        $this->index->waitTask($response['taskID']);

        $this->index->getRule('my-rule');
    }

    /**
     * @depends testSaveAndGetRule
     */
    public function testSearchRules()
    {
        $rule = $this->getRuleStub();
        $rule2 = $this->getRuleStub('my-second-rule');

        $response = $this->index->saveRule('my-rule', $rule);
        $this->index->waitTask($response['taskID']);

        $response = $this->index->saveRule('my-second-rule', $rule2);
        $this->index->waitTask($response['taskID']);

        $rules = $this->index->searchRules();
        $this->assertEquals(2, $rules['nbHits']);
    }

    /**
     * @depends testSaveAndGetRule
     * @depends testSearchRules
     */
    public function testBatchAndClearRules()
    {
        $rule = $this->getRuleStub();
        $rule2 = $this->getRuleStub('my-second-rule');

        $response = $this->index->batchRules(array($rule, $rule2));
        $this->index->waitTask($response['taskID']);

        $this->assertEquals($rule, $this->index->getRule('my-rule'));
        $this->assertEquals($rule2, $this->index->getRule('my-second-rule'));

        $response = $this->index->clearRules();
        $this->index->waitTask($response['taskID']);


        $rules = $this->index->searchRules();
        $this->assertEquals(0, $rules['nbHits']);
    }

    /**
     * @depends testBatchAndClearRules
     */
    public function testBatchClearExisting()
    {
        $rule = $this->getRuleStub();
        $rule2 = $this->getRuleStub('my-second-rule');
        $rule3 = $this->getRuleStub('my-second-rule-3');
        $rule4 = $this->getRuleStub('my-second-rule-4');

        $response = $this->index->batchRules(array($rule, $rule2));
        $this->index->waitTask($response['taskID']);

        $response = $this->index->batchRules(array($rule3, $rule4), false, true);
        $this->index->waitTask($response['taskID']);

        $rules = $this->index->searchRules();
        $this->assertEquals(2, $rules['nbHits']);

        unset($rules['hits'][0]['_highlightResult']);
        unset($rules['hits'][1]['_highlightResult']);

        $this->assertEquals(array($rule4, $rule3), $rules['hits']);
    }



    private function getRuleStub($objectID = 'my-rule')
    {
        return $rule = array(
            'objectID' => $objectID,
            'condition' => array(
                'pattern'   => 'some text',
                'anchoring' => 'is'
            ),
            'consequence' => array(
                'params' => array(
                    'query' => 'other text'
                )
            )
        );
    }
}
