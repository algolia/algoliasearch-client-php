<?php

namespace Algolia\AlgoliaSearch;

use Algolia\AlgoliaSearch\Exceptions\TaskTooLongException;
use Algolia\AlgoliaSearch\Interfaces\ClientConfigInterface;
use Algolia\AlgoliaSearch\Interfaces\IndexInterface;
use Algolia\AlgoliaSearch\RetryStrategy\ApiWrapper;
use Algolia\AlgoliaSearch\RequestOptions\RequestOptions;
use Algolia\AlgoliaSearch\Iterators\ObjectIterator;
use Algolia\AlgoliaSearch\Iterators\RuleIterator;
use Algolia\AlgoliaSearch\Iterators\SynonymIterator;
use Algolia\AlgoliaSearch\Support\ClientConfig;
use Algolia\AlgoliaSearch\Support\HttpLayer;
use Algolia\AlgoliaSearch\Support\Helpers;

class Index implements IndexInterface
{
    private $indexName;

    /**
     * @var ApiWrapper
     */
    protected $api;

    /**
     * @var ClientConfigInterface
     */
    protected $config;

    public function __construct($indexName, ApiWrapper $apiWrapper, ClientConfigInterface $config)
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

    public function clear($requestOptions = array())
    {
        return $this->api->write(
            'POST',
            api_path('/1/indexes/%s/clear', $this->indexName),
            array(),
            $requestOptions
        );
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
        return $this->api->write(
            'PUT',
            api_path('/1/indexes/%s/settings', $this->indexName),
            $settings,
            $requestOptions
        );
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
        Helpers::ensure_objectID($objects, 'All objects must have an unique objectID (like a primary key) to be valid.');

        return $this->batch(Helpers::build_batch($objects, 'addObject'), $requestOptions);
    }

    public function partialUpdateObject($object, $requestOptions = array())
    {
        return $this->partialUpdateObjects(array($object), $requestOptions);
    }

    public function partialUpdateObjects($objects, $requestOptions = array())
    {
        return $this->batch(Helpers::build_batch($objects, 'partialUpdateObjectNoCreate'), $requestOptions);
    }

    public function partialUpdateOrCreateObject($object, $requestOptions = array())
    {
        return $this->partialUpdateOrCreateObjects(array($object), $requestOptions);
    }

    public function partialUpdateOrCreateObjects($objects, $requestOptions = array())
    {
        return $this->batch(Helpers::build_batch($objects, 'partialUpdateObject'), $requestOptions);
    }

    public function freshObjects($objects, $requestOptions = array())
    {
        $tmpName = $this->indexName.'_tmp_'.uniqid();

        // TODO: Replica!
        $this->api->write(
            'POST',
            api_path('/1/indexes/%s/operation', $this->indexName),
            array(
                'operation' => 'copy',
                'destination' => $tmpName,
                'scope' => array('settings', 'synonyms', 'rules'),
            ),
            $requestOptions
        );

        $this->saveObjects($objects, $requestOptions);

        $this->api->write(
            'POST',
            api_path('/1/indexes/%s/operation', $this->indexName),
            array(
                'operation' => 'move',
                'destination' => $tmpName,
            ),
            $requestOptions
        );
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

        return $this->batch(Helpers::build_batch($objects, 'deleteObject'), $requestOptions);
    }

    public function deleteBy(array $args, $requestOptions = array())
    {
        return $this->api->write(
            'POST',
            api_path('/1/indexes/%s/deleteByQuery', $this->indexName),
            array('params' => Helpers::build_query($args)),
            $requestOptions
        );
    }

    public function batch($requests, $requestOptions = array())
    {
        return $this->api->write(
            'POST',
            api_path('/1/indexes/%s/batch', $this->indexName),
            array('requests' => $requests),
            $requestOptions
        );
    }

    public function browse($requestOptions = array())
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
        Helpers::ensure_objectID($synonyms, 'All synonyms must have an unique objectID to be valid');

        return $this->api->write(
            'POST',
            api_path('/1/indexes/%s/synonyms/batch', $this->indexName),
            $synonyms,
            $requestOptions
        );
    }

    public function freshSynonyms($synonyms, $requestOptions = array())
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
        return $this->api->write(
            'DELETE',
            api_path('/1/indexes/%s/synonyms/%s', $this->indexName, $objectId),
            array(),
            $requestOptions
        );
    }

    public function clearSynonyms($requestOptions = array())
    {
        return $this->api->write(
            'POST',
            api_path('/1/indexes/%s/synonyms/clear', $this->indexName),
            array(),
            $requestOptions
        );
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
        Helpers::ensure_objectID($rules, 'All rules must have an unique objectID to be valid');

        return $this->api->write(
            'POST',
            api_path('/1/indexes/%s/rules/batch', $this->indexName),
            $rules,
            $requestOptions
        );
    }

    public function freshRules($rules, $requestOptions = array())
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
        return $this->api->write(
            'DELETE',
            api_path('/1/indexes/%s/rules/%s', $this->indexName, $objectId),
            array(),
            $requestOptions
        );
    }

    public function clearRules($requestOptions = array())
    {
        return $this->api->write(
            'POST',
            api_path('/1/indexes/%s/rules/clear', $this->indexName),
            array(),
            $requestOptions
        );
    }

    public function browseRules($requestOptions = array())
    {
        return new RuleIterator($this->indexName, $this->api, $requestOptions);
    }

    public function getTask($taskId, $requestOptions = array())
    {
        return $this->api->read(
            'GET',
            api_path('/1/indexes/%s/task/%s', $this->indexName, $taskId),
            $requestOptions
        );
    }

    public function waitTask($taskId, $requestOptions = array())
    {
        $retry = 1;
        $maxRetry = $this->config->getWaitTaskMaxRetry();
        $time = $this->config->getWaitTaskTimeBeforeRetry();

        do {
            $res = $this->getTask($taskId, $requestOptions);

            if ('published' === $res['status']) {
                return;
            }

            $retry++;
            $factor = ceil($retry / 10);
            usleep($factor * $time); // 0.1 second
        } while ($retry < $maxRetry);

        throw new TaskTooLongException();
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
        return $this->api->write(
            'DELETE',
            api_path('/1/indexes/%s/keys/%s', $this->indexName, $key),
            array(),
            $requestOptions
        );
    }
}
