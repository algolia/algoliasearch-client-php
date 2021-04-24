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

    public function clickedFilters($eventName, $indexName, $filters, $requestOptions = [])
    {
        return $this->clicked(['filters' => $filters], $eventName, $indexName, $requestOptions);
    }

    public function clickedObjectIDs($eventName, $indexName, $objectIDs, $requestOptions = [])
    {
        return $this->clicked(['objectIDs' => $objectIDs], $eventName, $indexName, $requestOptions);
    }

    public function clickedObjectIDsAfterSearch($eventName, $indexName, $objectIDs, $positions, $queryID, $requestOptions = [])
    {
        $event = [
            'objectIDs' => $objectIDs,
            'positions' => $positions,
            'queryID' => $queryID,
        ];

        return $this->clicked($event, $eventName, $indexName, $requestOptions);
    }

    private function clicked($event, $eventName, $indexName, $requestOptions)
    {
        $event = array_merge($event, [
            'eventType' => 'click',
            'eventName' => $eventName,
            'index' => $indexName,
        ]);

        return $this->sendEvent($event, $requestOptions);
    }

    public function convertedFilters($eventName, $indexName, $filters, $requestOptions = [])
    {
        return $this->converted(['filters' => $filters], $eventName, $indexName, $requestOptions);
    }

    public function convertedObjectIDs($eventName, $indexName, $objectIDs, $requestOptions = [])
    {
        return $this->converted(['objectIDs' => $objectIDs], $eventName, $indexName, $requestOptions);
    }

    public function convertedObjectIDsAfterSearch($eventName, $indexName, $objectIDs, $queryID, $requestOptions = [])
    {
        $event = [
            'objectIDs' => $objectIDs,
            'queryID' => $queryID,
        ];

        return $this->converted($event, $eventName, $indexName, $requestOptions);
    }

    private function converted($event, $eventName, $indexName, $requestOptions)
    {
        $event = array_merge($event, [
            'eventType' => 'conversion',
            'eventName' => $eventName,
            'index' => $indexName,
        ]);

        return $this->sendEvent($event, $requestOptions);
    }

    public function viewedFilters($eventName, $indexName, $filters, $requestOptions = [])
    {
        $event = [
            'filters' => $filters,
        ];

        return $this->viewed($event, $eventName, $indexName, $requestOptions);
    }

    public function viewedObjectIDs($eventName, $indexName, $objectIDs, $requestOptions = [])
    {
        $event = [
            'objectIDs' => $objectIDs,
        ];

        return $this->viewed($event, $eventName, $indexName, $requestOptions);
    }

    private function viewed($event, $eventName, $indexName, $requestOptions)
    {
        $event = array_merge($event, [
            'eventType' => 'view',
            'eventName' => $eventName,
            'index' => $indexName,
        ]);

        return $this->sendEvent($event, $requestOptions);
    }

    private function sendEvent($event, $requestOptions = [])
    {
        return $this->client->sendEvent($this->reformatEvent($event), $requestOptions);
    }

    private function reformatEvent($e)
    {
        if (isset($e['objectIDs']) && !is_array($e['objectIDs'])) {
            $e['objectIDs'] = [$e['objectIDs']];
        }

        if (isset($e['filters']) && !is_array($e['filters'])) {
            $e['filters'] = [$e['filters']];
        }

        if (isset($e['positions']) && !is_array($e['positions'])) {
            $e['positions'] = [$e['positions']];
        }

        $e['userToken'] = $this->userToken;

        return $e;
    }
}
