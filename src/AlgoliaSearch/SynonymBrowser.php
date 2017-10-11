<?php

namespace AlgoliaSearch;


class SynonymBrowser implements \Iterator
{
    /**
     * @var Index
     */
    private $index;

    /**
     * @var int Zero-based page number
     */
    private $page;

    /**
     * @var int Number of results to return from each call to Algolia
     */
    private $hitsPerPage;

    /**
     * @var int Position in the current paginated result batch
     */
    private $position;

    /**
     * @var array Formatted current element
     */
    private $hit;

    /**
     * @var array Response from the last Algolia API call,
     * this contains the results for the current page.
     */
    private $response;

    /**
     * SynonymBrowser constructor.
     * @param Index $index
     * @param int $hitsPerPage
     */
    public function __construct($index, $hitsPerPage = 500)
    {
        $this->index = $index;
        $this->hitsPerPage = $hitsPerPage;

        $this->page = 0;
        $this->position = 0;

        $this->doQuery($page);
    }

    /**
     * Return the current element
     * @return array
     */
    public function current()
    {
        return $this->hit;
    }

    /**
     * Move forward to next element
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        $this->position++;
    }

    /**
     * Return the key of the current element
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        $key = $this->page * $this->hitsPerPage + $this->position;

        return $key;

    }

    /**
     * Checks if current position is valid. If the current position
     * is not valid, we call Algolia' API to load more results
     * until it's the last page.
     *
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        if (isset($this->response['hits'][$this->position])) {
            $this->hit = $this->formatHit($this->response['hits'][$this->position]);

            return true;
        }

        // No more results and it was the last page
        if (count($this->response['hits']) < $this->hitsPerPage) {
            return false;
        }

        // No more results but still results in Algolia
        // We call Algolia and loop back to this function
        $this->doQuery($this->page + 1);

        return $this->valid();
    }

    /**
     * Rewind the Iterator to the first element
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $this->page = 0;
        $this->position = 0;
        $this->hit = null;
    }

    /**
     * Call Algolia' API to get new result batch
     *
     * @param $page Page number to call
     */
    private function doQuery($page)
    {
        $this->page = $page;
        $this->position = 0;

        $this->response = $this->index->searchSynonyms('', array(), $page, $this->hitsPerPage);
    }

    /**
     * The export method is using search internally, this method
     * is used to clean the resuls, like remove the highlight
     *
     * @param array $hit
     * @return array formatted synonym array
     */
    private function formatHit(array $hit)
    {
        unset($hit['_highlightResult']);

        return $hit;
    }
}
