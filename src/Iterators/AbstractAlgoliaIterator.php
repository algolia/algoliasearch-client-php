<?php

namespace Algolia\AlgoliaSearch\Iterators;

use Algolia\AlgoliaSearch\RetryStrategy\ApiWrapper;

abstract class AbstractAlgoliaIterator implements \Iterator
{
    protected $indexName;

    /**
     * @var ApiWrapper
     */
    protected $api;

    /**
     * @var array RequestOptions passed when getting new batch from Algolia
     */
    protected $requestOptions;

    /**
     * @var int
     */
    protected $key = 0;

    /**
     * @var array response from the last Algolia API call,
     *            this contains the results for the current page
     */
    protected $response;

    public function __construct($indexName, ApiWrapper $api, $requestOptions = array())
    {
        $this->indexName = $indexName;
        $this->api = $api;
        $this->requestOptions = $requestOptions + array(
            'hitsPerPage' => 1000,
        );
    }

    /**
     * Return the current element.
     *
     * @return array
     */
    public function current()
    {
        $this->ensureResponseExists();
        $hit = $this->response['hits'][$this->getHitIndexForCurrentPage()];

        return $this->formatHit($hit);
    }

    /**
     * Move forward to next element.
     */
    public function next()
    {
        $previousPage = $this->getCurrentPage();
        $this->key++;
        if ($this->getCurrentPage() !== $previousPage) {
            // Discard the response if the page has changed.
            $this->response = null;
        }
    }

    /**
     * Return the key of the current element.
     *
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
     * @return bool The return value will be casted to boolean and then evaluated.
     *              Returns true on success or false on failure.
     */
    public function valid()
    {
        $this->ensureResponseExists();

        return isset($this->response['hits'][$this->getHitIndexForCurrentPage()]);
    }

    /**
     * Rewind the Iterator to the first element.
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
    protected function ensureResponseExists()
    {
        if (null === $this->response) {
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
        return (int) floor($this->key / ($this->requestOptions['hitsPerPage']));
    }

    /**
     * getHitIndexForCurrentPage retrieves the index
     * of the hit in the current page.
     *
     * @return int
     */
    protected function getHitIndexForCurrentPage()
    {
        return $this->key - ($this->getCurrentPage() * $this->requestOptions['hitsPerPage']);
    }

    /**
     * Call Algolia' API to get new result batch.
     */
    abstract protected function fetchCurrentPageResults();

    /**
     * Sometimes the Iterator is using search internally, this method
     * is used to clean the results, like remove the highlight.
     *
     * @param array $hit
     *
     * @return array formatted synonym array
     */
    abstract protected function formatHit(array $hit);
}
