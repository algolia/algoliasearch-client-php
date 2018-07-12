<?php

namespace Algolia\AlgoliaSearch\Tests\Integration;

class RulesTest extends AlgoliaIntegrationTestCase
{
    protected function setUp()
    {
        parent::setUp();

        if (!isset(static::$indexes['main'])) {
            static::$indexes['main'] = $this->safeName('rules-mgmt');
        }
    }

    public function testRulesCanBeSavedAndRetrieved()
    {
        $index = static::getClient()->index(static::$indexes['main']);

        $index->saveObject($this->airports[0]);

        $index->saveRule($this->getRuleStub('rule-1'));

        $index->saveRules(array($this->getRuleStub('rule-2'), $this->getRuleStub('rule-3')));

        $this->assertArraySubset($this->getRuleStub('rule-1'), $index->getRule('rule-1'));
        $this->assertArraySubset($this->getRuleStub('rule-3'), $index->getRule('rule-3'));

        $index->deleteRule('rule-1');

        $res = $index->searchRules('');
        $this->assertArraySubset(array('nbHits' => 2), $res);

        $index->freshRules(array($this->getRuleStub('rule-X')));
        $res = $index->searchRules('');
        $this->assertArraySubset(array('nbHits' => 1), $res);
        $this->assertArraySubset($this->getRuleStub('rule-X'), $res['hits'][0]);

        $index->clearRules();
        $res = $index->searchRules('');
        $this->assertArraySubset(array('nbHits' => 0), $res);
    }

    public function testBrowseRules()
    {
        $index = static::getClient()->index(static::$indexes['main']);

        $index->saveObject($this->airports[0]);

        $index->freshRules(array($this->getRuleStub('rule-1'), $this->getRuleStub('rule-2'), $this->getRuleStub('rule-3')));

        $previousObjectID = '';
        $iterator = $index->browseRules(array('hitsPerPage' => 1));
        foreach ($iterator as $rule) {
            $this->assertArraySubset(
                array(
                    'pattern'   => 'some text',
                    'anchoring' => 'is'
                ),
                $rule['condition']
            );
            $this->assertFalse($rule['objectID'] == $previousObjectID);
            $previousObjectID = $rule['objectID'];
        }
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
