<?php

namespace Algolia\AlgoliaSearch\Tests\Integration;

class RulesTest extends AlgoliaIntegrationTestCase
{
    protected function setUp()
    {
        parent::setUp();

        if (!isset(static::$indexes['main'])) {
            static::$indexes['main'] = self::safeName('rules-mgmt');
        }
    }

    public function testRulesCanBeSavedAndRetrieved()
    {
        /** @var \Algolia\AlgoliaSearch\SearchIndex $index */
        $index = static::getClient()->initIndex(static::$indexes['main']);

        $index->saveObject($this->airports[0]);

        $index->saveRule($this->getRuleStub('rule-1'));

        $index->saveRules(array($this->getRuleStub('rule-2'), $this->getRuleStub('rule-3')));

        $this->assertArraySubset($this->getRuleStub('rule-1'), $index->getRule('rule-1'));
        $this->assertArraySubset($this->getRuleStub('rule-3'), $index->getRule('rule-3'));

        $index->deleteRule('rule-1');

        $res = $index->searchRules('');
        $this->assertArraySubset(array('nbHits' => 2), $res);

        $index->replaceAllRules(array($this->getRuleStub('rule-X')));
        $res = $index->searchRules('');
        $this->assertArraySubset(array('nbHits' => 1), $res);
        $this->assertArraySubset($this->getRuleStub('rule-X'), $res['hits'][0]);

        $index->clearRules();
        $res = $index->searchRules('');
        $this->assertArraySubset(array('nbHits' => 0), $res);
    }

    public function testBrowseRules()
    {
        /** @var \Algolia\AlgoliaSearch\SearchIndex $index */
        $index = static::getClient()->initIndex(static::$indexes['main']);

        $index->saveObject($this->airports[0]);

        $index->replaceAllRules(array($this->getRuleStub('rule-1'), $this->getRuleStub('rule-2'), $this->getRuleStub('rule-3')));

        $previousObjectID = '';
        $i = 0;
        $iterator = $index->browseRules(array('hitsPerPage' => 1));
        foreach ($iterator as $rule) {
            $this->assertArraySubset(
                array(
                    'pattern' => 'some text',
                    'anchoring' => 'is',
                ),
                $rule['condition']
            );
            $this->assertNotEquals($rule['objectID'], $previousObjectID);
            $previousObjectID = $rule['objectID'];
            $i++;
        }

        $this->assertEquals(3, $i);
    }

    public function testSerializationOfConsequenceParams()
    {
        /** @var \Algolia\AlgoliaSearch\SearchIndex $index */
        $index = static::getClient()->initIndex(static::$indexes['main']);

        $rule = array(
            'objectID' => 'rule-without-consequence-params',
            'consequence' => array(
                // 'params' => array(),
                'hide' => array(array('objectID' => 'myID1')),
            ),
        );

        $index->saveRule($rule)->wait();
        self::assertArraySubset($rule, $index->getRule('rule-without-consequence-params'));

        $rule = array(
            'objectID' => 'rule-with-empty-consequence-params',
            'consequence' => array(
                'params' => array(),
                'hide' => array(array('objectID' => 'myID1')),
            ),
        );

        $index->saveRule($rule)->wait();
        // saveRule should unset params because is an empty list.
        unset($rule['consequence']['params']);
        self::assertArraySubset($rule, $index->getRule('rule-with-empty-consequence-params'));

        $rule = array(
            'objectID' => 'rule-with-consequence-params',
            'consequence' => array(
                'params' => array(
                    'filters' => 'category = 1',
                ),
                'hide' => array(array('objectID' => 'myID1')),
            ),
        );

        $index->saveRule($rule)->wait();
        self::assertArraySubset($rule, $index->getRule('rule-with-consequence-params'));
    }

    private function getRuleStub($objectID = 'my-rule')
    {
        return array(
            'objectID' => $objectID,
            'condition' => array(
                'pattern' => 'some text',
                'anchoring' => 'is',
            ),
            'consequence' => array(
                'params' => array(
                    'query' => 'other text',
                ),
            ),
        );
    }
}
