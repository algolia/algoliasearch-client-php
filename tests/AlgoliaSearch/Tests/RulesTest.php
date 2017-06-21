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
        $rule = array(
            'objectID' => 'my-rule',
            'if' => array(
                'pattern'   => 'some text',
                'anchoring' => 'is'
            ),
            'then' => array(
                'params' => array(
                    'query' => 'other text'
                )
            )
        );

        $response = $this->index->saveRule('my-rule', $rule);
        $this->index->waitTask($response['taskID']);

        $this->assertEquals($rule, $this->index->getRule('my-rule'));
    }

    /**
     * @expectedException AlgoliaSearch\AlgoliaException
     * @expectedExceptionMessage ObjectID does not exist
     */
    public function testDeleteRule()
    {
        $rule = array(
            'objectID' => 'my-rule',
            'if' => array(
                'pattern'   => 'some text',
                'anchoring' => 'is'
            ),
            'then' => array(
                'params' => array(
                    'query' => 'other text'
                )
            )
        );

        $response = $this->index->saveRule('my-rule', $rule);
        $this->index->waitTask($response['taskID']);

        $response = $this->index->deleteRule('my-rule');
        $this->index->waitTask($response['taskID']);

        $this->index->getRule('my-rule');
    }
}
