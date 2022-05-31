<?php

namespace Algolia\AlgoliaSearch\Response;

use Algolia\AlgoliaSearch\SearchIndex;

final class BatchIndexingResponse extends AbstractResponse implements \Iterator, \Countable
{
    /**
     * @var \Algolia\AlgoliaSearch\SearchIndex
     */
    private $index;

    /**
     * @var int Current index when class used as an iterator
     */
    private $key = 0;

    public function __construct(array $apiResponse, SearchIndex $index)
    {
        $this->apiResponse = array_values($apiResponse); // Ensure there aren't any keys
        $this->index = $index;
    }

    public function wait($requestOptions = [])
    {
        if (isset($this->index)) {
            foreach ($this->apiResponse as $response) {
                $this->index->waitTask($response['taskID'], $requestOptions);
            }
            unset($this->index);
        }

        return $this;
    }

    /**
     * Count response for the operations. Because indexing objects
     * is always split in batches, the apiResponse property and an
     * array of response from the API.
     *
     * @return number of response from the API (number of batches sent)
     */
    #[\ReturnTypeWillChange]
    public function count()
    {
        return count($this->apiResponse);
    }

    /**
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        return $this->apiResponse[$this->key];
    }

    /**
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function next()
    {
        $this->key++;
    }

    /**
     * @return bool|float|int|mixed|string|null
     */
    #[\ReturnTypeWillChange]
    public function key()
    {
        return $this->key;
    }

    /**
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function valid()
    {
        return isset($this->apiResponse[$this->key]);
    }

    /**
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function rewind()
    {
        $this->key = 0;
    }
}
