<?php

namespace Algolia\AlgoliaSearch;

use Algolia\AlgoliaSearch\Exceptions\TaskTooLongException;
use Algolia\AlgoliaSearch\Interfaces\Index as IndexInterface;
use Algolia\AlgoliaSearch\Internals\ApiWrapper;

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

    public function setSettings(
        $settings,
        $requestOptions = array(
            'forwardToReplicas' => true,
        )
    ) {
        $requestOptions += $settings;

        return $this->api->write(
            'PUT',
            api_path('/1/indexes/%s/settings', $this->indexName),
            $requestOptions
        );
    }

    public function addObjects($objects, $requestOptions = array())
    {
        $requestOptions['requests'] = $this->buildBatch('addObject', $objects);

        return $this->api->write(
            'POST',
            api_path('/1/indexes/%s/batch', $this->indexName),
            $requestOptions
        );
    }

    public function getSynonyms($objectID, $requestOptions = array())
    {
        return $this->api->read(
            'GET',
            api_path('/1/indexes/%s/synonyms/%s', $this->indexName, $objectID),
            $requestOptions
        );
    }

    public function clearSynonyms($forwardToReplicas = true, $requestOptions = array())
    {
        $requestOptions += array(
            'forwardToReplicas' => $forwardToReplicas,
        );

        return $this->api->write(
            'POST',
            api_path('/1/indexes/%s/synonyms/clear', $this->indexName),
            $requestOptions
        );
    }

    public function searchRules($requestOptions = array())
    {
        return $this->api->read(
            'POST',
            api_path('/1/indexes/%s/rules/search', $this->indexName),
            $requestOptions
        );
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

            if ($res['status'] === 'published') {
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

            if ($res['status'] === 'published') {
                return $res;
            }

            $retry++;
            $factor = ceil($retry/10);
            usleep($factor * 100000); // 0.1 second
        } while ($retry < $maxRetry);

        throw new TaskTooLongException;
    }

    private function buildBatch($action, $objects)
    {
        $operations = array();
        foreach ($objects as $obj) {
            $operations[] = array(
                'action' => $action,
                'body' => $obj,
            );
        }

        return $operations;
    }
}
