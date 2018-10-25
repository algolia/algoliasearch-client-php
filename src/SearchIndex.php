<?php

namespace Algolia\AlgoliaSearch;

use Algolia\AlgoliaSearch\Config\SearchConfig;
use Algolia\AlgoliaSearch\Interfaces\IndexContentInterface;
use Algolia\AlgoliaSearch\Response\IndexingObjectsResponse;
use Algolia\AlgoliaSearch\Response\IndexingResponse;
use Algolia\AlgoliaSearch\RetryStrategy\ApiWrapper;
use Algolia\AlgoliaSearch\RequestOptions\RequestOptions;
use Algolia\AlgoliaSearch\Iterators\ObjectIterator;
use Algolia\AlgoliaSearch\Iterators\RuleIterator;
use Algolia\AlgoliaSearch\Iterators\SynonymIterator;
use Algolia\AlgoliaSearch\Support\Helpers;

class SearchIndex
{
    private $indexName;

    /**
     * @var ApiWrapper
     */
    protected $api;

    /**
     * @var SearchConfig
     */
    protected $config;

    public function __construct($indexName, ApiWrapper $apiWrapper, SearchConfig $config)
    {
        $this->indexName = $indexName;
        $this->api = $apiWrapper;
        $this->config = $config;
    }

    public function getIndexName()
    {
        return $this->indexName;
    }

    public function setIndexName($indexName)
    {
        $this->indexName = $indexName;

        return $this;
    }

    public function search($query, $requestOptions = array())
    {
        if (is_array($requestOptions)) {
            $requestOptions['query'] = $query;
        } elseif ($requestOptions instanceof RequestOptions) {
            $requestOptions->addBodyParameter('query', $query);
        }

        return $this->api->read('POST', api_path('/1/indexes/%s/query', $this->indexName), $requestOptions);
    }

    public function searchForFacetValue($facetName, $facetQuery, $requestOptions = array())
    {
        if (is_array($requestOptions)) {
            $requestOptions['facetQuery'] = $facetQuery;
        } elseif ($requestOptions instanceof RequestOptions) {
            $requestOptions->addBodyParameter('facetQuery', $facetQuery);
        }

        return $this->api->read(
            'POST',
            api_path('/1/indexes/%s/facets/%s/query', $this->indexName, $facetName),
            $requestOptions
        );
    }

    public function move($newIndexName, $requestOptions = array())
    {
        $response = $this->api->write(
            'POST',
            api_path('/1/indexes/%s/operation', $this->indexName),
            array(
                'operation' => 'move',
                'destination' => $newIndexName,
            ),
            $requestOptions
        );

        $this->setIndexName($newIndexName);

        return new IndexingResponse($response, $this);
    }

    public function getSettings($requestOptions = array())
    {
        if (is_array($requestOptions)) {
            $requestOptions['getVersion'] = 2;
        } elseif ($requestOptions instanceof RequestOptions) {
            $requestOptions->addQueryParameter('getVersion', 2);
        }

        return $this->api->read(
            'GET',
            api_path('/1/indexes/%s/settings', $this->indexName),
            $requestOptions
        );
    }

    public function setSettings($settings, $requestOptions = array())
    {
        $default = array();
        if (is_bool($fwd = $this->config->getDefaultForwardToReplicas())) {
            $default['forwardToReplicas'] = $fwd;
        }

        $response = $this->api->write(
            'PUT',
            api_path('/1/indexes/%s/settings', $this->indexName),
            $settings,
            $requestOptions,
            $default
        );

        return new IndexingResponse($response, $this);
    }

    public function getObject($objectId, $requestOptions = array())
    {
        return $this->api->read(
            'GET',
            api_path('/1/indexes/%s/%s', $this->indexName, $objectId),
            $requestOptions
        );
    }

    public function getObjects($objectIds, $requestOptions = array())
    {
        if (is_array($requestOptions)) {
            $attributesToRetrieve = '';
            if (isset($requestOptions['attributesToRetrieve'])) {
                $attributesToRetrieve = $requestOptions['attributesToRetrieve'];
                unset($requestOptions['attributesToRetrieve']);
            }

            $request = array();
            foreach ($objectIds as $id) {
                $req = array(
                    'indexName' => $this->indexName,
                    'objectID' => (string) $id,
                );

                if ($attributesToRetrieve) {
                    $req['attributesToRetrieve'] = $attributesToRetrieve;
                }

                $request[] = $req;
            }

            $requestOptions['requests'] = $request;
        }

        return $this->api->read(
            'POST',
            api_path('/1/indexes/*/objects'),
            $requestOptions
        );
    }

    public function saveObject($object, $requestOptions = array())
    {
        return $this->saveObjects(array($object), $requestOptions);
    }

    public function saveObjects($objects, $requestOptions = array())
    {
        return $this->splitIntoBatches('addObject', $objects, $requestOptions);
    }

    public function partialUpdateObject($object, $requestOptions = array())
    {
        return $this->partialUpdateObjects(array($object), $requestOptions);
    }

    public function partialUpdateObjects($objects, $requestOptions = array())
    {
        $action = 'partialUpdateObjectNoCreate';

        if (isset($requestOptions['createIfNotExists']) && $requestOptions['createIfNotExists']) {
            $action = 'partialUpdateObject';
            unset($requestOptions['createIfNotExists']);
        }

        return $this->splitIntoBatches($action, $objects, $requestOptions);
    }

    public function replaceAllObjects($objects, $requestOptions = array())
    {
        $safe = isset($requestOptions['safe']) && $requestOptions['safe'];
        unset($requestOptions['safe']);
        $tmpName = $this->indexName.'_tmp_'.uniqid('php_', true);

        $client = new SearchClient($this->api, $this->config);

        // Copy all index resources from production index
        $copyResponse = $client->copyIndex($this->indexName, $tmpName, array(
            'scope' => array('settings', 'synonyms', 'rules')
        ));

        if ($safe) {
            $copyResponse->wait();
        }

        // Send records (batched automatically)
        $batchResponse = $this->saveObjects($objects, $requestOptions);

        if ($safe) {
            $batchResponse->wait();
        }

        // Move temporary index to production
        $moveResponse = $client->moveIndex($tmpName, $this->indexName);

        return array($copyResponse, $batchResponse, $moveResponse);
    }

    public function deleteObject($objectId, $requestOptions = array())
    {
        return $this->deleteObjects(array($objectId), $requestOptions);
    }

    public function deleteObjects($objectIds, $requestOptions = array())
    {
        $objects = array_map(function ($id) {
            return array('objectID' => $id);
        }, $objectIds);

        return $this->splitIntoBatches('deleteObject', $objects, $requestOptions);
    }

    public function deleteBy($filters, $requestOptions = array())
    {
        $response = $this->api->write(
            'POST',
            api_path('/1/indexes/%s/deleteByQuery', $this->indexName),
            $filters,
            $requestOptions
        );

        return new IndexingResponse($response, $this);
    }

    public function clearObjects($requestOptions = array())
    {
        $response = $this->api->write(
            'POST',
            api_path('/1/indexes/%s/clear', $this->indexName),
            array(),
            $requestOptions
        );

        return new IndexingResponse($response, $this);
    }

    public function batch($requests, $requestOptions = array())
    {
        $response = $this->rawBatch($requests, $requestOptions);

        return new IndexingResponse($response, $this);
    }

    protected function rawBatch($requests, $requestOptions = array())
    {
        return $this->api->write(
            'POST',
            api_path('/1/indexes/%s/batch', $this->indexName),
            array('requests' => $requests),
            $requestOptions
        );
    }

    protected function splitIntoBatches($action, $objects, $requestOptions = array())
    {
        $allResponses = array();
        $batch = array();
        $batchSize = $this->config->getBatchSize();
        $count = 0;

        foreach ($objects as $object) {
            $batch[] = $object;
            $count++;

            if ($count === $batchSize) {
                Helpers::ensureObjectID($batch, 'All objects must have an unique objectID (like a primary key) to be valid.');
                $allResponses[] = $this->rawBatch(Helpers::buildBatch($batch, $action), $requestOptions);
                $batch = array();
                $count = 0;
            }
        }

        return new IndexingObjectsResponse($allResponses, $this);
    }

    public function browseObjects($requestOptions = array())
    {
        return new ObjectIterator($this->indexName, $this->api, $requestOptions);
    }

    public function searchSynonyms($query, $requestOptions = array())
    {
        if (is_array($requestOptions)) {
            $requestOptions['query'] = $query;
        } elseif ($requestOptions instanceof RequestOptions) {
            $requestOptions->addBodyParameter('query', $query);
        }

        return $this->api->read(
            'POST',
            api_path('/1/indexes/%s/synonyms/search', $this->indexName),
            $requestOptions
        );
    }

    public function getSynonym($objectId, $requestOptions = array())
    {
        return $this->api->read(
            'GET',
            api_path('/1/indexes/%s/synonyms/%s', $this->indexName, $objectId),
            $requestOptions
        );
    }

    public function saveSynonym($synonym, $requestOptions = array())
    {
        return $this->saveSynonyms(array($synonym), $requestOptions);
    }

    public function saveSynonyms($synonyms, $requestOptions = array())
    {
        $default = array();
        if (is_bool($fwd = $this->config->getDefaultForwardToReplicas())) {
            $default['forwardToReplicas'] = $fwd;
        }

        if ($synonyms instanceof \Iterator) {
            $iteratedOver = array();
            foreach ($synonyms as $r) {
                $iteratedOver[] = $r;
            }
            $synonyms = $iteratedOver;
        }

        Helpers::ensureObjectID($synonyms, 'All synonyms must have an unique objectID to be valid');

        $response = $this->api->write(
            'POST',
            api_path('/1/indexes/%s/synonyms/batch', $this->indexName),
            $synonyms,
            $requestOptions,
            $default
        );

        return new IndexingResponse($response, $this);
    }

    public function replaceAllSynonyms($synonyms, $requestOptions = array())
    {
        if (is_array($requestOptions)) {
            $requestOptions['replaceExistingSynonyms'] = true;
        } elseif ($requestOptions instanceof RequestOptions) {
            $requestOptions->addQueryParameter('replaceExistingSynonyms', true);
        }

        return $this->saveSynonyms($synonyms, $requestOptions);
    }

    public function deleteSynonym($objectId, $requestOptions = array())
    {
        $default = array();
        if (is_bool($fwd = $this->config->getDefaultForwardToReplicas())) {
            $default['forwardToReplicas'] = $fwd;
        }

        $response = $this->api->write(
            'DELETE',
            api_path('/1/indexes/%s/synonyms/%s', $this->indexName, $objectId),
            array(),
            $requestOptions,
            $default
        );

        return new IndexingResponse($response, $this);
    }

    public function clearSynonyms($requestOptions = array())
    {
        $default = array();
        if (is_bool($fwd = $this->config->getDefaultForwardToReplicas())) {
            $default['forwardToReplicas'] = $fwd;
        }

        $response = $this->api->write(
            'POST',
            api_path('/1/indexes/%s/synonyms/clear', $this->indexName),
            array(),
            $requestOptions,
            $default
        );

        return new IndexingResponse($response, $this);
    }

    public function browseSynonyms($requestOptions = array())
    {
        return new SynonymIterator($this->indexName, $this->api, $requestOptions);
    }

    public function searchRules($query, $requestOptions = array())
    {
        if (is_array($requestOptions)) {
            $requestOptions['query'] = $query;
        } elseif ($requestOptions instanceof RequestOptions) {
            $requestOptions->addBodyParameter('query', $query);
        }

        return $this->api->read(
            'POST',
            api_path('/1/indexes/%s/rules/search', $this->indexName),
            $requestOptions
        );
    }

    public function getRule($objectId, $requestOptions = array())
    {
        return $this->api->read(
            'GET',
            api_path('/1/indexes/%s/rules/%s', $this->indexName, $objectId),
            $requestOptions
        );
    }

    public function saveRule($rule, $requestOptions = array())
    {
        return $this->saveRules(array($rule), $requestOptions);
    }

    public function saveRules($rules, $requestOptions = array())
    {
        $default = array();
        if (is_bool($fwd = $this->config->getDefaultForwardToReplicas())) {
            $default['forwardToReplicas'] = $fwd;
        }

        if ($rules instanceof \Iterator) {
            $iteratedOver = array();
            foreach ($rules as $r) {
                $iteratedOver[] = $r;
            }
            $rules = $iteratedOver;
        }

        Helpers::ensureObjectID($rules, 'All rules must have an unique objectID to be valid');

        $response = $this->api->write(
            'POST',
            api_path('/1/indexes/%s/rules/batch', $this->indexName),
            $rules,
            $requestOptions,
            $default
        );

        return new IndexingResponse($response, $this);
    }

    public function replaceAllRules($rules, $requestOptions = array())
    {
        if (is_array($requestOptions)) {
            $requestOptions['clearExistingRules'] = true;
        } elseif ($requestOptions instanceof RequestOptions) {
            $requestOptions->addQueryParameter('clearExistingRules', true);
        }

        return $this->saveRules($rules, $requestOptions);
    }

    public function deleteRule($objectId, $requestOptions = array())
    {
        $default = array();
        if (is_bool($fwd = $this->config->getDefaultForwardToReplicas())) {
            $default['forwardToReplicas'] = $fwd;
        }

        $response = $this->api->write(
            'DELETE',
            api_path('/1/indexes/%s/rules/%s', $this->indexName, $objectId),
            array(),
            $requestOptions,
            $default
        );

        return new IndexingResponse($response, $this);
    }

    public function clearRules($requestOptions = array())
    {
        $default = array();
        if (is_bool($fwd = $this->config->getDefaultForwardToReplicas())) {
            $default['forwardToReplicas'] = $fwd;
        }

        $response = $this->api->write(
            'POST',
            api_path('/1/indexes/%s/rules/clear', $this->indexName),
            array(),
            $requestOptions,
            $default
        );

        return new IndexingResponse($response, $this);
    }

    public function browseRules($requestOptions = array())
    {
        return new RuleIterator($this->indexName, $this->api, $requestOptions);
    }

    public function getTask($taskId, $requestOptions = array())
    {
        if (!$taskId) {
            throw new \InvalidArgumentException('taskID cannot be empty');
        }

        return $this->api->read(
            'GET',
            api_path('/1/indexes/%s/task/%s', $this->indexName, $taskId),
            $requestOptions
        );
    }

    public function waitTask($taskId, $requestOptions = array())
    {
        $retry = 1;
        $time = $this->config->getWaitTaskTimeBeforeRetry();

        do {
            $res = $this->getTask($taskId, $requestOptions);

            if ('published' === $res['status']) {
                return;
            }

            $retry++;
            $factor = ceil($retry / 10);
            usleep($factor * $time); // 0.1 second
        } while (true);
    }

    public function custom($method, $path, $requestOptions = array(), $hosts = null)
    {
        return $this->api->send($method, $path, $requestOptions, $hosts);
    }

    public function getDeprecatedIndexApiKey($key, $requestOptions = array())
    {
        return $this->api->read(
            'GET',
            api_path('/1/indexes/%s/keys/%s', $this->indexName, $key),
            $requestOptions
        );
    }

    public function deleteDeprecatedIndexApiKey($key, $requestOptions = array())
    {
        $response = $this->api->write(
            'DELETE',
            api_path('/1/indexes/%s/keys/%s', $this->indexName, $key),
            array(),
            $requestOptions
        );

        return new IndexingResponse($response, $this);
    }

    public function reindex(IndexContentInterface $indexContent, $wait = false)
    {
        $allResponses = array();
        $tmpIndexName = $this->indexName.'_tmp_'.uniqid('php_', true);
        $tmpIndex = new self($tmpIndexName, $this->api, $this->config);

        $settings = $indexContent->getSettings();
        $synonyms = $indexContent->getSynonyms();
        $rules = $indexContent->getRules();

        $allResponses[] = $this->initIndexForReindex($tmpIndexName, $settings, $synonyms, $rules);

        if ($settings) {
            $allResponses[] = $tmpIndex->setSettings($settings);
        }

        if ($synonyms) {
            $allResponses[] = $tmpIndex->saveSynonyms($synonyms);
        }

        if ($rules) {
            $allResponses[] = $tmpIndex->saveRules($rules);
        }

        $objectResponse = $this->saveObjects($indexContent->getObjects());
        $allResponses = array_merge($allResponses, $objectResponse);

        $allResponses = array_filter($allResponses);
        if ($wait) {
            foreach($allResponses as $response) {
                $response->wait();
            }
        }

        $moveResponse = $tmpIndex->move($this->indexName);

        if ($wait) {
            $moveResponse->wait();
        }
        $allResponses[] = $moveResponse;

        return $allResponses;
    }

    private function initIndexForReindex($tmpName, $settings, $synonyms, $rules)
    {
        $scope = array();

        if (!$settings) {
            $scope[] = 'settings';
        }

        if (!$synonyms) {
            $scope[] = 'synonyms';
        }

        if (!$rules) {
            $scope[] = 'rules';
        }

        if (empty($scope)) {
            return null;
        }

        return $this->api->write(
            'POST',
            api_path('/1/indexes/%s/operation', $this->indexName),
            array(
                'operation' => 'copy',
                'destination' => $tmpName,
                'scope' => array('settings', 'synonyms', 'rules'),
            )
        );
    }

    public function migrateTo($newAppId, $newApiKey)
    {
        $newIndex = SearchClient::create($newAppId, $newApiKey)->initIndex($this->indexName);

        $settings = $this->getSettings();
        $newIndex->setSettings($settings);

        $objectsIterator = $this->browseObjects();
        $newIndex->saveObjects($objectsIterator);

        $synonymsIterator = $this->browseSynonyms();
        $newIndex->saveSynonyms($synonymsIterator);

        $rulesIterator = $this->browseRules();
        $newIndex->saveRules($rulesIterator);
    }
}
