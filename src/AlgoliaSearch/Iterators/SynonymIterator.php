<?php

namespace AlgoliaSearch\Iterators;


class SynonymIterator extends AlgoliaIterator
{
    /**
     * The export method is using search internally, this method
     * is used to clean the results, like remove the highlight
     *
     * @param array $hit
     * @return array formatted synonym array
     */
    protected function formatHit(array $hit)
    {
        unset($hit['_highlightResult']);

        return $hit;
    }

    /**
     * Call Algolia' API to get new result batch
     */
    protected function fetchCurrentPageResults()
    {
        $this->response = $this->index->searchSynonyms('', array(), $this->getCurrentPage(), $this->hitsPerPage);
    }
}
