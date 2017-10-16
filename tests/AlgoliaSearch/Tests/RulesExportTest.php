<?php

namespace AlgoliaSearch\Tests;


use AlgoliaSearch\AlgoliaException;
use AlgoliaSearch\Client;
use AlgoliaSearch\Index;
use AlgoliaSearch\Iterators\RuleIterator;

class RulesExportTest extends AlgoliaSearchTestCase
{
    /** @var Client */
    private $client;

    /** @var Index */
    private $index;

    private $indexName = 'test-rule-export-php';

    protected function setUp()
    {
        $this->client = new Client(getenv('ALGOLIA_APPLICATION_ID'), getenv('ALGOLIA_API_KEY'));
        $this->index = $this->client->initIndex($this->indexName);
        $this->index->addObject(array('note' => 'Create index in Algolia'));

        try {
            $res = $this->index->clearRules();
            $this->index->waitTask($res['taskID'], 0.1);
        } catch (AlgoliaException $e) {
            // not fatal
        }
    }

    protected function tearDown()
    {
        try {
            $this->client->deleteIndex($this->indexName);
        } catch (AlgoliaException $e) {
            // not fatal
        }
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testShouldRejectInvalidHitsPerPage()
    {
        new RuleIterator($this->index, 0);
    }

    public function testCanGetCurrentRuleOfNewIterator()
    {
        $stub = $this->getRuleStub('stub-1');
        $res = $this->index->saveRule('stub-1', $stub);
        $this->index->waitTask($res['taskID'], 0.1);

        $rule = $this->index->initRuleIterator()->current();

        $this->assertEquals($stub, $rule);
    }

    public function testRulesExport()
    {
        $res = $this->index->batchRules(array(
            $this->getRuleStub('rule-1'),
            $this->getRuleStub('rule-2'),
            $this->getRuleStub('rule-3'),
        ));
        $this->index->waitTask($res['taskID'], 0.1);

        $exported = array();
        $iterator = $this->index->initRuleIterator(2);

        $i = 0;
        foreach ($iterator as $key => $rule) {
            $this->assertArrayNotHasKey('_highlightResult', $rule);
            $this->assertEquals($i++, $key);

            $exported[] = $rule;
        }

        $this->assertCount(3, $exported);
    }

    public function testFoundRulesCanBeBatched()
    {
        $res = $this->index->batchRules(array(
            $this->getRuleStub('rule-1'),
            $this->getRuleStub('rule-2'),
        ));
        $this->index->waitTask($res['taskID'], 0.1);


        $browser = $this->index->initRuleIterator();

        $rules = array();
        foreach ($browser as $key => $rule) {
            $rules[] = $rule;
        }

        $res = $this->index->clearRules();
        $this->index->waitTask($res['taskID']);

        $this->index->batchRules($rules);
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
