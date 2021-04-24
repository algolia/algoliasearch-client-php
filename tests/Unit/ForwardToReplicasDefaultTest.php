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

        $methods = [
            'setSettings' => [],
            'saveSynonym' => ['objectID' => 'xx'],
            'saveRule' => ['objectID' => 'xx'],
            'saveSynonyms' => [['objectID' => 'xx']],
            'saveRules' => [['objectID' => 'xx']],
            'replaceAllSynonyms' => [['objectID' => 'xx']],
            'replaceAllRules' => [['objectID' => 'xx']],
            'deleteSynonym' => 'id',
            'deleteRule' => 'id',
            'clearSynonyms' => [],
            'clearRules' => [],
        ];

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
        $index = SearchClient::createWithConfig(new SearchConfig([
            'defaultForwardToReplicas' => $defaultValue,
        ]))->initIndex('test');

        $methods = [
            'setSettings' => [],
            'saveSynonym' => ['objectID' => 'xx'],
            'saveRule' => ['objectID' => 'xx'],
            'saveSynonyms' => [['objectID' => 'xx']],
            'saveRules' => [['objectID' => 'xx']],
            'replaceAllSynonyms' => [['objectID' => 'xx']],
            'replaceAllRules' => [['objectID' => 'xx']],
            'deleteSynonym' => 'id',
            'deleteRule' => 'id',
            'clearSynonyms' => [],
            'clearRules' => [],
        ];

        foreach ($methods as $methodName => $arg1) {
            try {
                $index->{$methodName}($arg1);
            } catch (RequestException $e) {
                $this->assertQueryParametersSubset(
                    ['forwardToReplicas' => $defaultValue ? 'true' : 'false'],
                    $e->getRequest()
                );
            }
        }
    }

    public function provideConfigDefaultValue()
    {
        return [
            [true], [false],
        ];
    }
}
