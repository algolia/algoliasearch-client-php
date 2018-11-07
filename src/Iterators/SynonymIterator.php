<?php

namespace Algolia\AlgoliaSearch\Iterators;

use Algolia\AlgoliaSearch\Support\Helpers;

final class SynonymIterator extends AbstractAlgoliaIterator
{
    protected function formatHit(array $hit)
    {
        unset($hit['_highlightResult']);

        return $hit;
    }

    protected function fetchNextPage()
    {
        if (is_array($this->response) && $this->key >= $this->response['nbHits']) {
            return;
        }

        $this->response = $this->api->read(
            'POST',
            Helpers::apiPath('/1/indexes/%s/synonyms/search', $this->indexName),
            array_merge(
                $this->requestOptions,
                array('page' => $this->page)
            )
        );

        $this->batchKey = 0;
        $this->page++;
    }
}
