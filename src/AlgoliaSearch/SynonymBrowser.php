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
    private $page = 0;

    /**
     * @var int Number of results to return from each call to Algolia
     */
    private $hitsPerPage;

    /**
     * @var int Position in the current paginated result batch
     */
    private $position = 0;

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
    public function __construct(Index $index, $hitsPerPage = 1000)
    {
        $this->index = $index;
        $this->hitsPerPage = $hitsPerPage;
    }

    /**
     * Return the current element
     * @return array
     */
    public function current()
    {
        $this->ensureResponseExists();

        return $this->formatHit($this->response['hits'][$this->position]);
    }

    /**
     * Move forward to next element
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        $this->ensureResponseExists();

        $this->position++;

        if ($this->valid()) {
            return;
        }

        // If the end of the batch is reached but there are
        // more results in Algolia, we get the next page.
        if (count($this->response['hits']) >= $this->hitsPerPage) {
            $this->page++;
            $this->position = 0;
            $this->fetchCurrentPageResults();
        }
    }

    /**
     * Return the key of the current element
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return $this->page * $this->hitsPerPage + $this->position;
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
        $this->ensureResponseExists();

        return isset($this->response['hits'][$this->position]);
    }

    /**
     * Rewind the Iterator to the first element
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $this->page = 0;
        $this->position = 0;
        $this->response = null;

        $this->fetchCurrentPageResults();
    }

    /**
     * Call Algolia' API to get new result batch
     *
     * @param $page Page number to call
     */
    private function fetchCurrentPageResults()
    {
        $this->response = $this->index->searchSynonyms('', array(), $this->page, $this->hitsPerPage);
    }

    /**
     * The export method is using search internally, this method
     * is used to clean the results, like remove the highlight
     *
     * @param array $hit
     * @return array formatted synonym array
     */
    private function formatHit(array $hit)
    {
        unset($hit['_highlightResult']);

        return $hit;
    }

    /**
     * ensureResponseExists is always called prior
     * to trying to access the response property.
     */
    private function ensureResponseExists() {
        if ($this->response === null) {
            $this->fetchCurrentPageResults();
        }
    }
}
