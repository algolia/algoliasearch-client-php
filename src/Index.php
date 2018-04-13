<?php

namespace Algolia\AlgoliaSearch;

use Algolia\AlgoliaSearch\Interfaces\Index as IndexInterface;
use Algolia\AlgoliaSearch\Internals\ApiWrapper;

final class Index implements IndexInterface
{
    private $indexName;
    private $urlIndexName;

    /**
     * @var ApiWrapper
     */
    private $api;

    public function __construct($indexName, ApiWrapper $apiWrapper)
    {
        $this->indexName = $indexName;
        $this->urlIndexName = urlencode($indexName);
        $this->api = $apiWrapper;
    }

    public function addObjects($objects, $requestOptions = array())
    {
        $requestOptions['requests'] = $this->buildBatch('addObject', $objects);

        return $this->api->write(
            'POST',
            '/1/indexes/'.$this->urlIndexName.'/batch',
            $requestOptions
        );
    }

    public function getSynonyms($objectID, $requestOptions = array())
    {
        return $this->api->read(
            'GET',
            '/1/indexes/'.$this->urlIndexName.'/synonyms/'.urlencode($objectID),
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
            '/1/indexes/'.$this->urlIndexName.'/synonyms/clear',
            $requestOptions
        );
    }

    public function searchRules($requestOptions = array())
    {
        return $this->api->read(
            'POST',
            '/1/indexes/'.$this->urlIndexName.'/rules/search',
            $requestOptions
        );
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
