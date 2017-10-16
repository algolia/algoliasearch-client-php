<?php

namespace AlgoliaSearch\Iterators;


use AlgoliaSearch\Index;

abstract class AlgoliaIterator implements \Iterator
{
    /**
     * @var Index
     */
    protected $index;

    /**
     * @var int Number of results to return from each call to Algolia
     */
    protected $hitsPerPage;

    /**
     * @var int
     */
    protected $key = 0;

    /**
     * @var array Response from the last Algolia API call,
     * this contains the results for the current page.
     */
    protected $response;

    /**
     * Iterator constructor.
     *
     * @param Index $index
     * @param int $hitsPerPage
     */
    public function __construct(Index $index, $hitsPerPage = 1000)
    {
        if ($hitsPerPage <= 0) {
            throw new \InvalidArgumentException('Hits per page should be bigger than zero.');
        }

        $this->index = $index;
        $this->hitsPerPage = (int) $hitsPerPage;
    }

    /**
     * Return the current element
     * @return array
     */
    public function current()
    {
        $this->ensureResponseExists();
        $hit = $this->response['hits'][$this->getHitIndexForCurrentPage()];

        return $this->formatHit($hit);
    }

    /**
     * Move forward to next element
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        $previousPage = $this->getCurrentPage();
        $this->key++;
        if($this->getCurrentPage() !== $previousPage) {
            // Discard the response if the page has changed.
            $this->response = null;
        }
    }

    /**
     * Return the key of the current element
     * @return int
     */
    public function key()
    {
        return $this->key;
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

        return isset($this->response['hits'][$this->getHitIndexForCurrentPage()]);
    }

    /**
     * Rewind the Iterator to the first element
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $this->key = 0;
        $this->response = null;
    }

    /**
     * ensureResponseExists is always called prior
     * to trying to access the response property.
     */
    protected function ensureResponseExists() {
        if ($this->response === null) {
            $this->fetchCurrentPageResults();
        }
    }

    /**
     * getCurrentPage returns the current zero based page according to
     * the current key and hits per page.
     *
     * @return int
     */
    protected function getCurrentPage()
    {
        return (int) floor($this->key / ($this->hitsPerPage));
    }

    /**
     * getHitIndexForCurrentPage retrieves the index
     * of the hit in the current page.
     *
     * @return int
     */
    protected function getHitIndexForCurrentPage()
    {
        return $this->key - ($this->getCurrentPage() * $this->hitsPerPage);
    }

    /**
     * Call Algolia' API to get new result batch
     */
    abstract protected function fetchCurrentPageResults();

    /**
     * The export method might be is using search internally, this method
     * is used to clean the results, like remove the highlight
     *
     * @param array $hit
     * @return array formatted synonym array
     */
    abstract protected function formatHit(array $hit);
}
