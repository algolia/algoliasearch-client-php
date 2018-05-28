<?php

namespace Algolia\AlgoliaSearch;

use Algolia\AlgoliaSearch\Exceptions\TaskTooLongException;
use Algolia\AlgoliaSearch\Interfaces\Index as IndexInterface;
use Algolia\AlgoliaSearch\Internals\ApiWrapper;
use Algolia\AlgoliaSearch\Iterators\RuleIterator;
use Algolia\AlgoliaSearch\Iterators\SynonymIterator;

final class Index implements IndexInterface
{
    private $indexName;

    /**
     * @var ApiWrapper
     */
    private $api;

    public function __construct($indexName, ApiWrapper $apiWrapper)
    {
        $this->indexName = $indexName;
        $this->api = $apiWrapper;
    }

    public function search($query, $requestOptions = array())
    {
        $requestOptions['query'] = $query;

        return $this->api->read('POST', api_path('/1/indexes/%s/query', $this->indexName), $requestOptions);
    }

    public function clear($requestOptions = array())
    {
        return $this->api->write(
            'POST',
            api_path('/1/indexes/%s/clear', $this->indexName),
            $requestOptions
        );
    }

    public function getSettings($requestOptions = array())
    {
        $requestOptions['getVersion'] = 2;

        return $this->api->read(
            'GET',
            api_path('/1/indexes/%s/settings', $this->indexName),
            $requestOptions
        );
    }

    public function setSettings($settings, $requestOptions = array(
            'forwardToReplicas' => true,
    ))
    {
        $requestOptions += $settings;

        return $this->api->write(
            'PUT',
            api_path('/1/indexes/%s/settings', $this->indexName),
            $requestOptions
        );
    }

    public function getObject($objectId, $requestOptions = array(
        'attributesToRetrieve' => array(),
    ))
    {
        return $this->api->read(
            'GET',
            api_path('/1/indexes/%s/%s', $this->indexName, $objectId),
            $requestOptions
        );
    }

    public function getObjects($objectIds, $requestOptions = array(
        'attributesToRetrieve' => array(),
    ))
    {
        $attributesToRetrieve = '';
        if ($requestOptions['attributesToRetrieve']) {
            $attributesToRetrieve = $requestOptions['attributesToRetrieve'];
        }
        if (is_array($attributesToRetrieve)) {
            $attributesToRetrieve = implode(',', $attributesToRetrieve);
        }

        $requests = array();
        foreach ($objectIds as $id) {
            $req = array(
                'indexName' => $this->indexName,
                'objectID' => $id,
            );

            if ($attributesToRetrieve) {
                $req['attributesToRetrieve'] = $attributesToRetrieve;
            }

            $requests[] = $req;
        }

        return $this->api->read(
            'POST',
            api_path('/1/indexes/*/objects'),
            $requestOptions
        );
    }

    public function addObject($object, $requestOptions = array())
    {
        return $this->addObjects(array($object), $requestOptions);
    }

    public function addObjects($objects, $requestOptions = array())
    {
        return $this->batch(build_batch($objects, 'addObject'), $requestOptions);
    }

    public function updateObject($object, $requestOptions = array(
        'createIfNotExists' => true,
    ))
    {
        return $this->updateObjects(array($object), $requestOptions);
    }

    public function updateObjects($objects, $requestOptions = array(
        'createIfNotExists' => true,
    ))
    {
        $create = isset($requestOptions['createIfNotExists']) ? $requestOptions['createIfNotExists'] : true;

        $action = $create ? 'partialUpdateObject' : 'partialUpdateObjectNoCreate';

        return $this->batch(build_query($objects, $action), $requestOptions);
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

        return $this->batch(build_batch($objects, 'deleteObject'), $requestOptions);
    }

    public function deleteBy(array $args, $requestOptions = array())
    {
        $requestOptions['params'] = build_query($args);

        return $this->api->write(
            'POST',
            api_path('/1/indexes/%s/deleteByQuery', $this->indexName),
            $requestOptions
        );
    }

    public function batch($requests, $requestOptions = array())
    {
        $requestOptions['requests'] = $requests;

        return $this->api->write(
            'POST',
            api_path('/1/indexes/%s/batch', $this->indexName),
            $requestOptions
        );
    }

    public function searchSynonyms($query, $requestOptions = array(
        'type' => array(),
        'page' => 0,
    ))
    {
        $requestOptions['query'] = $query;

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

    public function saveSynonym($synonym, $requestOptions = array(
        'forwardToReplicas' => true,
    ))
    {
        return $this->saveSynonyms(array($synonym), $requestOptions);
    }

    public function saveSynonyms($synonyms, $requestOptions = array(
        'forwardToReplicas' => true,
    ))
    {
        $requestOptions = array_merge($synonyms, $requestOptions);

        return $this->api->write(
            'POST',
            api_path('/1/indexes/%s/synonyms/batch', $this->indexName),
            $requestOptions
        );
    }

    public function freshSynonyms($synonyms, $requestOptions = array(
        'forwardToReplicas' => true,
    ))
    {
        $requestOptions['replaceExistingSynonyms'] = true;

        return $this->saveSynonyms($synonyms, $requestOptions);
    }

    public function deleteSynonym($objectId, $requestOptions = array(
        'forwardToReplicas' => true,
    ))
    {
        return $this->api->write(
            'DELETE',
            api_path('/1/indexes/%s/synonyms/%s', $this->indexName, $objectId),
            $requestOptions
        );
    }

    public function clearSynonyms($requestOptions = array(
        'forwardToReplicas' => true,
    ))
    {
        return $this->api->write(
            'POST',
            api_path('/1/indexes/%s/synonyms/clear', $this->indexName),
            $requestOptions
        );
    }

    public function browseSynonyms($requestOptions = array())
    {
        return new SynonymIterator($this, $requestOptions);
    }

    public function searchRules($query, $requestOptions = array(
        'page' => 0,
    ))
    {
        $requestOptions['query'] = $query;

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

    public function saveRule($rule, $requestOptions = array(
        'forwardToReplicas' => true,
    ))
    {
        return $this->saveRules(array($rule), $requestOptions);
    }

    public function saveRules($rules, $requestOptions = array(
        'forwardToReplicas' => true,
    ))
    {
        $requestOptions = array_merge($rules, $requestOptions);

        return $this->api->write(
            'POST',
            api_path('/1/indexes/%s/rules/batch', $this->indexName),
            $requestOptions
        );
    }

    public function freshRules($rules, $requestOptions = array(
        'forwardToReplicas' => true,
    ))
    {
        $requestOptions['clearExistingRules'] = true;

        return $this->saveRules($rules, $requestOptions);
    }

    public function deleteRule($objectId, $requestOptions = array(
        'forwardToReplicas' => true,
    ))
    {
        return $this->api->write(
            'DELETE',
            api_path('/1/indexes/%s/rules/%s', $this->indexName, $objectId),
            $requestOptions
        );
    }

    public function clearRules($requestOptions = array(
        'forwardToReplicas' => true,
    ))
    {
        return $this->api->write(
            'POST',
            api_path('/1/indexes/%s/rules/clear', $this->indexName),
            $requestOptions
        );
    }

    public function browseRules($requestOptions = array())
    {
        return new RuleIterator($this, $requestOptions);
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
        do {
            $res = $this->getTask($taskId, $requestOptions);

            if ('published' === $res['status']) {
                return $res;
            }

            usleep(100000); // 0.1 second
        } while (true);
    }

    public function ProposalWaitTask($taskId, $requestOptions = array())
    {
        $retry = 1;
        $maxRetry = Config::$waitTaskRetry;

        do {
            $res = $this->getTask($taskId, $requestOptions);

            if ('published' === $res['status']) {
                return $res;
            }

            ++$retry;
            $factor = ceil($retry / 10);
            usleep($factor * 100000); // 0.1 second
        } while ($retry < $maxRetry);

        throw new TaskTooLongException();
    }

    public function getDeprecatedIndexApiKey($key)
    {
        return $this->api->write('GET', api_path('/1/indexes/%s/keys/%s', $this->indexName, $key));
    }

    public function deleteDeprecatedIndexApiKey($key)
    {
        return $this->api->write('DELETE', api_path('/1/indexes/%s/keys/%s', $this->indexName, $key));
    }
}
