<?php

namespace Algolia\AlgoliaSearch\Response;

class MultiResponse extends AbstractResponse implements \Iterator, \Countable
{
    protected $key = 0;

    public function __construct($responses)
    {
        $this->apiResponse = $responses;
    }

    public function wait($requestOptions = array())
    {
        foreach ($this->apiResponse as $response) {
            $response->wait();
        }

        return $this;
    }

    public function count()
    {
        return count($this->apiResponse);
    }

    public function current()
    {
        return $this->apiResponse[$this->key];
    }

    public function next()
    {
        $this->key++;
    }

    public function key()
    {
        return $this->key;
    }

    public function valid()
    {
        return isset($this->apiResponse[$this->key]);
    }

    public function rewind()
    {
        $this->key = 0;
    }
}
