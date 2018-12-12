<?php

namespace Algolia\AlgoliaSearch;

use Algolia\AlgoliaSearch\Config\InsightsConfig;
use Algolia\AlgoliaSearch\RetryStrategy\ApiWrapper;
use Algolia\AlgoliaSearch\RetryStrategy\ClusterHosts;

final class InsightsClient
{
    /**
     * @var \Algolia\AlgoliaSearch\RetryStrategy\ApiWrapper
     */
    protected $api;

    /**
     * @var \Algolia\AlgoliaSearch\Config\InsightsConfig
     */
    protected $config;

    public function __construct(ApiWrapper $api, InsightsConfig $config)
    {
        $this->api = $api;
        $this->config = $config;
    }

    public static function create($appId = null, $apiKey = null, $region = null, $userToken = null)
    {
        $config = InsightsConfig::create($appId, $apiKey, $region);

        if ($userToken) {
            $config->setUserToken($config);
        }

        return static::createWithConfig($config);
    }

    public static function createWithConfig(InsightsConfig $config)
    {
        $config = clone $config;


        if ($hosts = $config->getHosts()) {
            // If a list of hosts was passed, we ignore the cache
            $clusterHosts = ClusterHosts::create($hosts);
        } else {
            $clusterHosts = ClusterHosts::createForInsights($config->getRegion());
        }

        $apiWrapper = new ApiWrapper(
            Algolia::getHttpClient(),
            $config,
            $clusterHosts
        );

        return new static($apiWrapper, $config);
    }

    public function user($userToken)
    {
        $config = clone $this->config;
        $config->setUserToken($userToken);

        return new self($this->api, $config);
    }

    public function clickedFilters($eventName, $indexName, $filters, $requestOptions = array())
    {
        return $this->clicked(array('filters' => $filters), $eventName, $indexName, $requestOptions);
    }

    public function clickedObjectIDs($eventName, $indexName, $objectIDs, $requestOptions = array())
    {
        return $this->clicked(array('objectIDs' => $objectIDs), $eventName, $indexName, $requestOptions);
    }

    public function clickedObjectIDsAfterSearch($eventName, $indexName, $objectIDs, $queryID, $positions, $requestOptions = array())
    {
        $event = array(
            'objectIDs' => $objectIDs,
            'queryID' => $queryID,
            'positions' => $positions,
        );

        return $this->clicked($event, $eventName, $indexName, $requestOptions);
    }

    private function clicked($event, $eventName, $indexName, $requestOptions)
    {
        $event = array_merge($event, array(
            'type' => 'click',
            'eventName' => $eventName,
            'index' => $indexName,
        ));

        return $this->sendEvent($event, $requestOptions);
    }

    public function convertedObjectIDs($eventName, $indexName, $objectIDs, $requestOptions = array())
    {
        return $this->converted(array('objectsIDs' => $objectIDs), $eventName, $indexName, $requestOptions);
    }

    public function convertedObjectIDsAfterSearch($eventName, $indexName, $objectIDs, $queryID, $requestOptions = array())
    {
        $event = array(
            'objectsIDs' => $objectIDs,
            'queryID' => $queryID,
        );

        return $this->converted($event, $eventName, $indexName, $requestOptions);
    }

    private function converted($event, $eventName, $indexName, $requestOptions)
    {
        $event = array_merge($event, array(
            'eventType' => 'conversion',
            'eventName' => $eventName,
            'index' => $indexName,
        ));

        return $this->sendEvent($event, $requestOptions);
    }

    public function viewedObjectIDs($eventName, $indexName, $objectIDs, $requestOptions = array())
    {
        $event = array(
            'objectsIDs' => $objectIDs,
        );

        return $this->viewed($event, $eventName, $indexName, $requestOptions);
    }

    public function viewedFilters($eventName, $indexName, $filters, $requestOptions = array())
    {
        $event = array(
            'filters' => $filters,
        );

        return $this->viewed($event, $eventName, $indexName, $requestOptions);
    }

    private function viewed($event, $eventName, $indexName, $requestOptions)
    {
        $event = array_merge($event, array(
            'eventType' => 'view',
            'eventName' => $eventName,
            'index' => $indexName,
        ));

        return $this->sendEvent($event, $requestOptions);
    }

    public function sendEvent($event, $requestOptions = array())
    {
        if (isset($requestOptions['timestamp'])) {
            $event['timestamp'] = $requestOptions['timestamp'];
            unset($requestOptions['timestamp']);
        }

        return $this->sendEvents(array($event), $requestOptions);
    }

    public function sendEvents($events, $requestOptions = array())
    {
        $events = array_map(array($this, 'reformatEvent'), $events);

        $payload = array('events' => $events);

        return $this->api->write('POST', api_path('/1/events'), $payload, $requestOptions);
    }

    private function reformatEvent($e)
    {
        if (isset($e['objectIDs']) && !is_array($e['objectIDs'])) {
            $e['objectIDs'] = array($e['objectIDs']);
        }

        if (isset($e['positions']) && !is_array($e['positions'])) {
            $e['positions'] = array($e['positions']);
        }

        if (!isset($e['userToken'])) {
            $e['userToken'] = $this->config->getUserToken();
        }

        return $e;
    }
}
