<?php

namespace Algolia\AlgoliaSearch\Insights;

final class PersoInsightClient extends AbstractInsightsClient
{

    public function clickedObjectID($eventName, $indexName, $objectIDs, $positions, $requestOptions = array())
    {
        $clickEvent = array(
            'objectsIDs' => is_array($objectIDs) ? $objectIDs : array($objectIDs),
            'positions' => is_array($positions) ? $positions : array($positions),
        );

        return $this->clicked($clickEvent, $eventName, $indexName, $requestOptions);
    }

    public function clickedFilters($eventName, $indexName, $filters, $requestOptions = array())
    {
        $clickEvent = array(
            'filters' => is_array($filters) ? $filters : array($filters),
        );

        return $this->clicked($clickEvent, $eventName, $indexName, $requestOptions);
    }

    private function clicked($clickEvent, $eventName, $indexName, $requestOptions = array())
    {
        $clickEvent = array_merge($clickEvent, array(
            'eventType' => 'click',
            'eventName' => $eventName,
            'index' => $indexName,
        ));

        return $this->sendEvent($clickEvent, $requestOptions);
    }

    public function viewedObjectID($eventName, $indexName, $objectIDs, $requestOptions = array())
    {
        $clickEvent = array(
            'objectsIDs' => is_array($objectIDs) ? $objectIDs : array($objectIDs),
        );

        return $this->viewed($clickEvent, $eventName, $indexName, $requestOptions);
    }

    public function viewedFilters($eventName, $indexName, $filters, $requestOptions = array())
    {
        $clickEvent = array(
            'filters' => is_array($filters) ? $filters : array($filters),
        );

        return $this->viewed($clickEvent, $eventName, $indexName, $requestOptions);
    }

    private function viewed($viewEvent, $eventName, $indexName, $requestOptions = array())
    {
        $viewEvent = array_merge($viewEvent, array(
            'eventType' => 'conversion',
            'eventName' => $eventName,
            'index' => $indexName,
        ));

        return $this->sendEvent($viewEvent, $requestOptions);
    }

    public function converted($eventName, $indexName, $objectIDs, $requestOptions = array())
    {
        $renameEvent = array(
            'eventType' => 'conversion',
            'eventName' => $eventName,
            'index' => $indexName,
            'objectsIDs' => is_array($objectIDs) ? $objectIDs : array($objectIDs),
        );

        return $this->sendEvent($renameEvent, $requestOptions);
    }
}
