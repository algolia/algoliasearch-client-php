<?php

namespace AlgoliaSearch\Tests;

use AlgoliaSearch\AlgoliaException;
use AlgoliaSearch\Client;
use AlgoliaSearch\Index;

class MoveIndexTest extends AlgoliaSearchTestCase
{
    /** @var Client */
    private $client;

    /** @var Index */
    private $index;

    protected function setUp()
    {
        $this->client = new Client(getenv('ALGOLIA_APPLICATION_ID'), getenv('ALGOLIA_API_KEY'));
        $this->index = $this->client->initIndex($this->safe_name('àlgol?à-php'));
        try {
            $this->index->clearIndex();
        } catch (AlgoliaException $e) {
            // not fatal
        }
        try {
            $this->client->deleteIndex($this->safe_name('àlgol?à2-php'));
            //$this->client->waitTask($task['taskID']);
        } catch (AlgoliaException $e) {
            // CopyIndex does not exist
        }
    }

    protected function tearDown()
    {
        try {
            $this->client->deleteIndex($this->safe_name('àlgol?à-php'));
        } catch (AlgoliaException $e) {
            // not fatal
        }
        try {
            $this->client->deleteIndex($this->safe_name('àlgol?à2-php'));
        } catch (AlgoliaException $e) {
            // not fatal
        }
    }

    public function includeValue($tab, $attrName, $value)
    {
        foreach ($tab as $key => $elt) {
            if ($elt[$attrName] == $value) {
                return true;
            }
        }

        return false;
    }

    public function testMoveIndex()
    {
        $task = $this->index->addObject(array('firstname' => 'Robin'));
        $this->index->waitTask($task['taskID']);

        $task = $this->client->moveIndex($this->safe_name('àlgol?à-php'), $this->safe_name('àlgol?à2-php'));
        $this->index = $this->client->initIndex($this->safe_name('àlgol?à2-php'));
        $this->index->waitTask($task['taskID']);

        $res = $this->index->search('');
        $list = $this->client->listIndexes();
        $this->assertTrue($this->includeValue($list['items'], 'name', $this->safe_name('àlgol?à2-php')));
        $this->assertFalse($this->includeValue($list['items'], 'name', $this->safe_name('àlgol?à-php')));
        $this->assertEquals(1, $res['nbHits']);
        $this->assertEquals('Robin', $res['hits'][0]['firstname']);
    }

    public function testCopyIndex()
    {
        $this->index2 = $this->client->initIndex($this->safe_name('àlgol?à2-php'));
        $task = $this->index2->addObject(array('firstname' => 'Robin'));
        $this->index2->waitTask($task['taskID']);

        $this->expectOutputString('');
        $task = $this->client->copyIndex($this->safe_name('àlgol?à2-php'), $this->safe_name('àlgol?à-php'));
        $this->index->waitTask($task['taskID']);

        $this->index = $this->client->initIndex($this->safe_name('àlgol?à-php'));
        $this->index2 = $this->client->initIndex($this->safe_name('àlgol?à2-php'));

        $res = $this->index->search('');
        $this->assertEquals(1, $res['nbHits']);
        $del = $this->index->deleteObject($res['hits'][0]['objectID']);
        $this->index->waitTask($del['taskID']);

        $res = $this->index2->search('');

        $this->assertEquals(1, $res['nbHits']);
        $this->assertEquals('Robin', $res['hits'][0]['firstname']);
    }

    public function testScopedCopyIndex()
    {
        // Create source index with records, settings, synonyms and rules
        $srcIndexName = $this->safe_name('àlgol?à-scoped-copy-index-php');
        $this->client->deleteIndex($srcIndexName);
        $srcIndex = $this->client->initIndex($srcIndexName);
        $task = $srcIndex->addObjects(array(
            array('firstname' => 'Robin'),
            array('firstname' => 'Julien'),
        ));
        $taskSettings = $srcIndex->setSettings(array(
            'searchableAttributes' => array('firstname')
        ));
        $taskRules = $srcIndex->saveRule('my-rule', array(
            'condition' => array(
                'pattern'   => 'some text',
                'anchoring' => 'is'
            ),
            'consequence' => array(
                'params' => array(
                    'query' => 'other text'
                )
            )
        ));
        $taskSynonym = $srcIndex->saveSynonym('my-synonym', array(
            'type'     => 'synonym',
            'synonyms' => array('San Francisco', 'SF'),
        ));

        $srcIndex->waitTask($task['taskID']);
        $srcIndex->waitTask($taskSettings['taskID']);
        $srcIndex->waitTask($taskRules['taskID']);
        $srcIndex->waitTask($taskSynonym['taskID']);

        // If no scope is passed, all resources are copied
        $task = $this->client->scopedCopyIndex($srcIndexName, $this->safe_name($srcIndexName.'_no_scope'));
        $destIndex = $this->client->initIndex($this->safe_name($srcIndexName.'_no_scope'));
        $destIndex->waitTask($task['taskID']);

        $res = $destIndex->search('');
        $this->assertEquals(2, $res['nbHits']);
        $settings = $destIndex->getSettings();
        $this->assertArraySubset(array('searchableAttributes' => array('firstname')), $settings);
        $rules = $destIndex->searchRules();
        $this->assertEquals(1, $rules['nbHits']);
        $synonym = $destIndex->searchSynonyms('');
        $this->assertEquals(1, $synonym['nbHits']);

        // If any scope is passed, only these resources are copied
        $task = $this->client->scopedCopyIndex(
            $srcIndexName,
            $this->safe_name($srcIndexName.'_rules'),
            array('settings', 'rules')
        );
        $destIndex = $this->client->initIndex($this->safe_name($srcIndexName.'_rules'));
        $destIndex->waitTask($task['taskID']);

        $res = $destIndex->search('');
        $this->assertEquals(0, $res['nbHits']);
        $settings = $destIndex->getSettings();
        $this->assertArraySubset(array('searchableAttributes' => array('firstname')), $settings);
        $rules = $destIndex->searchRules();
        $this->assertEquals(1, $rules['nbHits']);
        $synonym = $destIndex->searchSynonyms('');
        $this->assertEquals(0, $synonym['nbHits']);

        // Clean up
        $this->client->deleteIndex($srcIndexName);
        $this->client->deleteIndex($this->safe_name($srcIndexName.'_no_scope'));
        $this->client->deleteIndex($this->safe_name($srcIndexName.'_rules'));
    }
}
