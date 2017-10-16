<?php

namespace AlgoliaSearch\Iterators;


use AlgoliaSearch\Index;

class RuleIterator extends AlgoliaIterator
{
    public function __construct(Index $index, $hitsPerPage = 500)
    {
        parent::__construct($index, $hitsPerPage);
    }

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
        $this->response = $this->index->searchRules(array(
            'hitsPerPage' => $this->hitsPerPage,
            'page' => $this->getCurrentPage(),
        ));
    }
}
