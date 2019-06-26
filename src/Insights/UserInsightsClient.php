<?php

namespace Algolia\AlgoliaSearch\Insights;

use Algolia\AlgoliaSearch\InsightsClient;
use Algolia\AlgoliaSearch\RequestOptions\RequestOptions;

final class UserInsightsClient
{
    /**
     * @var InsightsClient
     */
    private $client;

    /**
     * @var string
     */
    private $userToken;

    /**
     * UserInsightsClient constructor.
     *
     * @param InsightsClient $client
     * @param string         $userToken
     */
    public function __construct(InsightsClient $client, $userToken)
    {
        $this->client = $client;
        $this->userToken = $userToken;
    }

    /**
     * @param string $eventName
     * @param string $indexName
     * @param array  $filters
     * @param array  $requestOptions
     *
     * @return array
     */
    public function clickedFilters($eventName, $indexName, $filters, $requestOptions = array())
    {
        return $this->clicked(array('filters' => $filters), $eventName, $indexName, $requestOptions);
    }

    /**
     * @param string $eventName
     * @param string $indexName
     * @param array  $objectIDs
     * @param array  $requestOptions
     *
     * @return array
     */
    public function clickedObjectIDs($eventName, $indexName, $objectIDs, $requestOptions = array())
    {
        return $this->clicked(array('objectIDs' => $objectIDs), $eventName, $indexName, $requestOptions);
    }

    /**
     * @param string $eventName
     * @param string $indexName
     * @param array  $objectIDs
     * @param array  $positions
     * @param string $queryID
     * @param array  $requestOptions
     *
     * @return array
     */
    public function clickedObjectIDsAfterSearch($eventName, $indexName, $objectIDs, $positions, $queryID, $requestOptions = array())
    {
        $event = array(
            'objectIDs' => $objectIDs,
            'positions' => $positions,
            'queryID' => $queryID,
        );

        return $this->clicked($event, $eventName, $indexName, $requestOptions);
    }

    /**
     * @param array                $event
     * @param string               $eventName
     * @param string               $indexName
     * @param array|RequestOptions $requestOptions
     *
     * @return array
     */
    private function clicked($event, $eventName, $indexName, $requestOptions)
    {
        $event = array_merge($event, array(
            'eventType' => 'click',
            'eventName' => $eventName,
            'index' => $indexName,
        ));

        return $this->sendEvent($event, $requestOptions);
    }

    /**
     * @param string $eventName
     * @param string $indexName
     * @param array  $filters
     * @param array  $requestOptions
     *
     * @return array
     */
    public function convertedFilters($eventName, $indexName, $filters, $requestOptions = array())
    {
        return $this->converted(array('filters' => $filters), $eventName, $indexName, $requestOptions);
    }

    /**
     * @param string $eventName
     * @param string $indexName
     * @param array  $objectIDs
     * @param array  $requestOptions
     *
     * @return array
     */
    public function convertedObjectIDs($eventName, $indexName, $objectIDs, $requestOptions = array())
    {
        return $this->converted(array('objectIDs' => $objectIDs), $eventName, $indexName, $requestOptions);
    }

    /**
     * @param string $eventName
     * @param string $indexName
     * @param array  $objectIDs
     * @param string $queryID
     * @param array  $requestOptions
     *
     * @return array
     */
    public function convertedObjectIDsAfterSearch($eventName, $indexName, $objectIDs, $queryID, $requestOptions = array())
    {
        $event = array(
            'objectIDs' => $objectIDs,
            'queryID' => $queryID,
        );

        return $this->converted($event, $eventName, $indexName, $requestOptions);
    }

    /**
     * @param array                $event
     * @param string               $eventName
     * @param string               $indexName
     * @param array|RequestOptions $requestOptions
     *
     * @return array
     */
    private function converted($event, $eventName, $indexName, $requestOptions)
    {
        $event = array_merge($event, array(
            'eventType' => 'conversion',
            'eventName' => $eventName,
            'index' => $indexName,
        ));

        return $this->sendEvent($event, $requestOptions);
    }

    /**
     * @param string $eventName
     * @param string $indexName
     * @param array  $filters
     * @param array  $requestOptions
     *
     * @return array
     */
    public function viewedFilters($eventName, $indexName, $filters, $requestOptions = array())
    {
        $event = array(
            'filters' => $filters,
        );

        return $this->viewed($event, $eventName, $indexName, $requestOptions);
    }

    /**
     * @param string $eventName
     * @param string $indexName
     * @param array  $objectIDs
     * @param array  $requestOptions
     *
     * @return array
     */
    public function viewedObjectIDs($eventName, $indexName, $objectIDs, $requestOptions = array())
    {
        $event = array(
            'objectIDs' => $objectIDs,
        );

        return $this->viewed($event, $eventName, $indexName, $requestOptions);
    }

    /**
     * @param array                $event
     * @param string               $eventName
     * @param string               $indexName
     * @param array|RequestOptions $requestOptions
     *
     * @return array
     */
    private function viewed($event, $eventName, $indexName, $requestOptions)
    {
        $event = array_merge($event, array(
            'eventType' => 'view',
            'eventName' => $eventName,
            'index' => $indexName,
        ));

        return $this->sendEvent($event, $requestOptions);
    }

    /**
     * @param array $event
     * @param array $requestOptions
     *
     * @return array
     */
    private function sendEvent($event, $requestOptions = array())
    {
        return $this->client->sendEvent($this->reformatEvent($event), $requestOptions);
    }

    /**
     * @param array $event
     *
     * @return array
     */
    private function reformatEvent($event)
    {
        if (isset($event['objectIDs']) && !is_array($event['objectIDs'])) {
            $event['objectIDs'] = array($event['objectIDs']);
        }

        if (isset($event['filters']) && !is_array($event['filters'])) {
            $event['filters'] = array($event['filters']);
        }

        if (isset($event['positions']) && !is_array($event['positions'])) {
            $event['positions'] = array($event['positions']);
        }

        $event['userToken'] = $this->userToken;

        return $event;
    }
}
