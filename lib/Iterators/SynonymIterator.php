<?php

namespace Algolia\AlgoliaSearch\Iterators;

final class SynonymIterator extends AbstractAlgoliaIterator
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
            && $this->key >= $this->response['nbHits']
        ) {
            return;
        }

        $this->response = $this->searchClient->searchSynonyms(
            $this->indexName,
            null,
            $this->page,
            $this->requestOptions['hitsPerPage'],
            $this->requestOptions
        );

        $this->batchKey = 0;
        ++$this->page;
    }
}
