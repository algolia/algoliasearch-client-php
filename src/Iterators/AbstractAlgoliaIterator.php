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
     * @var int
     */
    protected $batchKey = 0;

    /**
     * @var int
     */
    protected $page = 0;

    /**
     * @var array response from the last Algolia API call,
     *            this contains the results for the current page
     */
    protected $response;

    /**
     * Call Algolia' API to get new result batch.
     */
    abstract protected function fetchNextPage();

    /**
     * Sometimes the Iterator is using search internally, this method
     * is used to clean the results, like remove the highlight.
     *
     * @return array formatted synonym array
     */
    abstract protected function formatHit(array $hit);

    public function __construct($indexName, ApiWrapper $api, $requestOptions = array())
    {
        $this->indexName = $indexName;
        $this->api = $api;
        $this->requestOptions = $requestOptions + array(
            'hitsPerPage' => 1000,
        );

        $this->fetchNextPage();
    }

    /**
     * Return the current element.
     *
     * @return array
     */
    public function current()
    {
        $hit = $this->response['hits'][$this->batchKey];

        return $this->formatHit($hit);
    }

    /**
     * Move forward to next element.
     */
    public function next()
    {
        $this->key++;
        $this->batchKey++;
        if ($this->valid()) {
            return;
        }

        $this->fetchNextPage();
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
     * @return bool the return value will be casted to boolean and then evaluated.
     *              Returns true on success or false on failure
     */
    public function valid()
    {
        return isset($this->response['hits'][$this->batchKey]);
    }

    /**
     * Rewind the Iterator to the first element.
     */
    public function rewind()
    {
        if (0 !== $this->key) {
            $this->key = 0;
            $this->batchKey = 0;
            $this->page = 0;
            $this->response = null;
            $this->fetchNextPage();
        }
    }
}
