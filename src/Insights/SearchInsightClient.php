<?php

namespace Algolia\AlgoliaSearch\Insights;

final class SearchInsightClient extends AbstractInsightsClient
{
    protected $queryId;

    public function setQueryId($queryId)
    {
        $this->queryId = $queryId;

        return $this;
    }
}
