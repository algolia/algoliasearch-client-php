<?php

namespace Algolia\AlgoliaSearch\Insights;

use Algolia\AlgoliaSearch\InsightsClient;

final class UserInsightsClient
{
    /**
     * @var \Algolia\AlgoliaSearch\InsightsClient
     */
    private $client;

    private $userToken;

    public function __construct(InsightsClient $client, $userToken)
    {
        $this->client = $client;
        $this->userToken = $userToken;
    }

    public function clickedFilters($eventName, $indexName, $filters, $requestOptions = array())
    {
        return $this->clicked(array('filters' => $filters), $eventName, $indexName, $requestOptions);
    }

    public function clickedObjectIDs($eventName, $indexName, $objectIDs, $requestOptions = array())
    {
        return $this->clicked(array('objectIDs' => $objectIDs), $eventName, $indexName, $requestOptions);
    }

    public function clickedObjectIDsAfterSearch($eventName, $indexName, $objectIDs, $positions, $queryID, $requestOptions = array())
    {
        $event = array(
            'objectIDs' => $objectIDs,
            'positions' => $positions,
            'queryID' => $queryID,
        );

        return $this->clicked($event, $eventName, $indexName, $requestOptions);
    }

    private function clicked($event, $eventName, $indexName, $requestOptions)
    {
        $event = array_merge($event, array(
            'eventType' => 'click',
            'eventName' => $eventName,
            'index' => $indexName,
        ));

        return $this->sendEvent($event, $requestOptions);
    }

    public function convertedFilters($eventName, $indexName, $filters, $requestOptions = array())
    {
        return $this->converted(array('filters' => $filters), $eventName, $indexName, $requestOptions);
    }

    public function convertedObjectIDs($eventName, $indexName, $objectIDs, $requestOptions = array())
    {
        return $this->converted(array('objectIDs' => $objectIDs), $eventName, $indexName, $requestOptions);
    }

    public function convertedObjectIDsAfterSearch($eventName, $indexName, $objectIDs, $queryID, $requestOptions = array())
    {
        $event = array(
            'objectIDs' => $objectIDs,
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

    public function viewedFilters($eventName, $indexName, $filters, $requestOptions = array())
    {
        $event = array(
            'filters' => $filters,
        );

        return $this->viewed($event, $eventName, $indexName, $requestOptions);
    }

    public function viewedObjectIDs($eventName, $indexName, $objectIDs, $requestOptions = array())
    {
        $event = array(
            'objectIDs' => $objectIDs,
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

    private function sendEvent($event, $requestOptions = array())
    {
        return $this->client->sendEvent($this->reformatEvent($event), $requestOptions);
    }

    private function reformatEvent($e)
    {
        if (isset($e['objectIDs']) && !is_array($e['objectIDs'])) {
            $e['objectIDs'] = array($e['objectIDs']);
        }

        if (isset($e['filters']) && !is_array($e['filters'])) {
            $e['filters'] = array($e['filters']);
        }

        if (isset($e['positions']) && !is_array($e['positions'])) {
            $e['positions'] = array($e['positions']);
        }

        $e['userToken'] = $this->userToken;

        return $e;
    }
}
