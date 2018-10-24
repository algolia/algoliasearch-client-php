<?php

namespace Algolia\AlgoliaSearch\Tests\Unit;

use Algolia\AlgoliaSearch\Client;
use Algolia\AlgoliaSearch\Config\ClientConfig;
use Algolia\AlgoliaSearch\Exceptions\RequestException;

class ForwardToReplicasDefaultTest extends RequestTestCase
{
    public function testIndexDoesNotSetForwardToReplicasByDefault()
    {
        /** @var \Algolia\AlgoliaSearch\Index $index */
        $index = static::$client->initIndex('test');

        $methods = array(
            'setSettings' => array(),
            'saveSynonym' => array('objectID' => 'xx'),
            'saveRule' => array('objectID' => 'xx'),
            'saveSynonyms' => array(array('objectID' => 'xx')),
            'saveRules' => array(array('objectID' => 'xx')),
            'replaceAllSynonyms' => array(array('objectID' => 'xx')),
            'replaceRules' => array(array('objectID' => 'xx')),
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
            'replaceAllSynonyms' => array(array('objectID' => 'xx')),
            'replaceRules' => array(array('objectID' => 'xx')),
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
