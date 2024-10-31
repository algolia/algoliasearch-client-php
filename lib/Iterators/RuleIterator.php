<?php

namespace Algolia\AlgoliaSearch\Iterators;

final class RuleIterator extends AbstractAlgoliaIterator
{
    protected function formatHit(array $hit)
    {
        unset($hit['_highlightResult']);

        return $hit;
    }

    protected function fetchNextPage()
    {
        if (
            is_array($this->response)
            && $this->key >= count($this->response['hits'])
        ) {
            return;
        }

        $this->response = $this->searchClient->searchRules(
            $this->indexName,
            array_merge($this->requestOptions, ['page' => $this->page])
        );

        $this->batchKey = 0;
        ++$this->page;
    }
}
