<?php

namespace Algolia\AlgoliaSearch\Tests\Unit;

use Algolia\AlgoliaSearch\Config\SearchConfig;
use Algolia\AlgoliaSearch\Exceptions\RequestException;
use Algolia\AlgoliaSearch\SearchClient;

class ForwardToReplicasDefaultTest extends RequestTestCase
{
    public function testIndexDoesNotSetForwardToReplicasByDefault()
    {
        /** @var \Algolia\AlgoliaSearch\SearchIndex $index */
        $index = SearchClient::create('id', 'key')->initIndex('test');

        $methods = array(
            'setSettings' => array(),
            'saveSynonym' => array('objectID' => 'xx'),
            'saveRule' => array('objectID' => 'xx'),
            'saveSynonyms' => array(array('objectID' => 'xx')),
            'saveRules' => array(array('objectID' => 'xx')),
            'replaceAllSynonyms' => array(array('objectID' => 'xx')),
            'replaceAllRules' => array(array('objectID' => 'xx')),
            'deleteSynonym' => 'id',
            'deleteRule' => 'id',
            'clearSynonyms' => array(),
            'clearRules' => array(),
        );

        foreach ($methods as $methodName => $arg1) {
            try {
                $index->{$methodName}($arg1);
            } catch (RequestException $e) {
                $this->assertQueryParametersNotHasKey('forwardToReplicas', $e->getRequest());
            }
        }
    }

    /**
     * @dataProvider provideConfigDefaultValue
     */
    public function testIndexUseConfigDefaultForwardToReplicas($defaultValue)
    {
        /** @var \Algolia\AlgoliaSearch\SearchIndex $index */
        $index = SearchClient::createWithConfig(new SearchConfig(array(
            'defaultForwardToReplicas' => $defaultValue,
        )))->initIndex('test');

        $methods = array(
            'setSettings' => array(),
            'saveSynonym' => array('objectID' => 'xx'),
            'saveRule' => array('objectID' => 'xx'),
            'saveSynonyms' => array(array('objectID' => 'xx')),
            'saveRules' => array(array('objectID' => 'xx')),
            'replaceAllSynonyms' => array(array('objectID' => 'xx')),
            'replaceAllRules' => array(array('objectID' => 'xx')),
            'deleteSynonym' => 'id',
            'deleteRule' => 'id',
            'clearSynonyms' => array(),
            'clearRules' => array(),
        );

        foreach ($methods as $methodName => $arg1) {
            try {
                $index->{$methodName}($arg1);
            } catch (RequestException $e) {
                $this->assertQueryParametersSubset(
                    array('forwardToReplicas' => $defaultValue ? 'true' : 'false'),
                    $e->getRequest()
                );
            }
        }
    }

    public function provideConfigDefaultValue()
    {
        return array(
            array(true), array(false),
        );
    }
}
