<?php

namespace Algolia\AlgoliaSearch\Insights;

use Algolia\AlgoliaSearch\Config\InsightsConfig;
use Algolia\AlgoliaSearch\RetryStrategy\ApiWrapper;
use Algolia\AlgoliaSearch\Support\Helpers;

abstract class AbstractInsightsClient
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

    public function click($clickEvent, $requestOptions = array())
    {
        $clickEvent['eventType'] = 'click';

        return $this->addEvent($clickEvent, $requestOptions);
    }

    public function conversion($renameEvent, $requestOptions = array())
    {
        $renameEvent['eventType'] = 'conversion';

        return $this->addEvent($renameEvent, $requestOptions);
    }

    public function addEvent($event, $requestOptions = array())
    {
        return $this->addEvents(array($event), $requestOptions);
    }

    public function addEvents($events, $requestOptions = array())
    {
        $events = array_map(array($this, 'reformatEvent'), $events);

        $payload = array('events' => $events);

        return $this->api->write('POST', Helpers::apiPath('/1/events'), $payload, $requestOptions);
    }

    private function reformatEvent($e)
    {
        if (!isset($e['timestamp'])) {
            $e['timestamp'] = time();
        }

        if (isset($e['objectID'])) {
            if (!isset($e['objectIDs'])) {
                $e['objectIDs'] = array($e['objectID']);
            }
            unset($e['objectID']);
        }

        if (isset($e['position'])) {
            if (!isset($e['positions'])) {
                $e['positions'] = array($e['position']);
            }
            unset($e['position']);
        }

        if (!isset($e['userToken'])) {
            $e['userToken'] = $this->config->getUserToken();
        }

        if (isset($this->queryId) && !isset($e['queryID'])) {
            $e['queryID'] = $this->queryId;
        }

        return $e;
    }
}
