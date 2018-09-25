<?php

namespace Algolia\AlgoliaSearch\Tests\Endpoint;

use Algolia\AlgoliaSearch\Client;
use Algolia\AlgoliaSearch\Support\ClientConfig;

class ForwardToReplicasDefaultTest extends RequestTestCase
{
    public function testIndexDoesNotSetForwardToReplicasByDefault()
    {
        /** @var \Algolia\AlgoliaSearch\Index $index */
        $index = Client::get()->initIndex('test');

        $methods = array(
            'setSettings' => array(),
            'saveSynonym' => array('objectID' => 'xx'),
            'saveRule' => array('objectID' => 'xx'),
            'saveSynonyms' => array(array('objectID' => 'xx')),
            'saveRules' => array(array('objectID' => 'xx')),
            'freshSynonyms' => array(array('objectID' => 'xx')),
            'freshRules' => array(array('objectID' => 'xx')),
            'deleteSynonym' => 'id',
            'deleteRule' => 'id',
            'clearSynonyms' => array(),
            'clearRules' => array(),
        );

        foreach ($methods as $methodName => $arg1) {
            list($request, $timeout, $connectTimeout) = $index->{$methodName}($arg1);

            $this->assertQueryParametersNotHasKey('forwardToReplicas', $request);
        }
    }

    /**
     * @dataProvider provideConfigDefaultValue
     */
    public function testIndexUseConfigDefaultForwardToReplicas($defaultValue)
    {
        /** @var \Algolia\AlgoliaSearch\Index $index */
        $index = Client::createWithConfig(new ClientConfig(array(
            'defaultForwardToReplicas' => $defaultValue,
        )))->initIndex('test');

        $methods = array(
            'setSettings' => array(),
            'saveSynonym' => array('objectID' => 'xx'),
            'saveRule' => array('objectID' => 'xx'),
            'saveSynonyms' => array(array('objectID' => 'xx')),
            'saveRules' => array(array('objectID' => 'xx')),
            'freshSynonyms' => array(array('objectID' => 'xx')),
            'freshRules' => array(array('objectID' => 'xx')),
            'deleteSynonym' => 'id',
            'deleteRule' => 'id',
            'clearSynonyms' => array(),
            'clearRules' => array(),
        );

        foreach ($methods as $methodName => $arg1) {
            list($request, $timeout, $connectTimeout) = $index->{$methodName}($arg1);

            $this->assertQueryParametersSubset(
                array('forwardToReplicas' => $defaultValue ? 'true' : 'false'),
                $request
            );
        }
    }

    public function provideConfigDefaultValue()
    {
        return array(
            array(true), array(false),
        );
    }
}
