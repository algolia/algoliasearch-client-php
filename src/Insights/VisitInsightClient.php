<?php

namespace Algolia\AlgoliaSearch\Insights;

class VisitInsightClient extends AbstractInsightsClient
{
    public function view($viewEvent, $requestOptions = array())
    {
        $viewEvent['eventType'] = 'view';

        return $this->addEvent($viewEvent, $requestOptions);
    }
}
