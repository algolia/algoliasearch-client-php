<?php

namespace Algolia\AlgoliaSearch\Iterators;

class RuleIterator extends AlgoliaIterator
{
    protected function formatHit(array $hit)
    {
        unset($hit['_highlightResult']);

        return $hit;
    }

    protected function fetchCurrentPageResults()
    {
        $this->response = $this->api->read(
            'POST',
            api_path('/1/indexes/%s/rules/search', $this->indexName),
            array_merge(
                $this->requestOptions,
                array('page' => $this->getCurrentPage())
            )
        );
    }
}
