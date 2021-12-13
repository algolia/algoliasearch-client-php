<?php

namespace Algolia\AlgoliaSearch\Response;

class MultiResponse extends AbstractResponse implements \Iterator, \Countable
{
    protected $key = 0;

    public function __construct($responses)
    {
        $this->apiResponse = $responses;
    }

    public function wait($requestOptions = [])
    {
        foreach ($this->apiResponse as $response) {
            $response->wait();
        }

        return $this;
    }

    #[\ReturnTypeWillChange]
    public function count()
    {
        return count($this->apiResponse);
    }

    #[\ReturnTypeWillChange]
    public function current()
    {
        return $this->apiResponse[$this->key];
    }

    #[\ReturnTypeWillChange]
    public function next()
    {
        $this->key++;
    }

    #[\ReturnTypeWillChange]
    public function key()
    {
        return $this->key;
    }

    #[\ReturnTypeWillChange]
    public function valid()
    {
        return isset($this->apiResponse[$this->key]);
    }

    #[\ReturnTypeWillChange]
    public function rewind()
    {
        $this->key = 0;
    }
}
