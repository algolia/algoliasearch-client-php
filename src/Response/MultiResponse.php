<?php

namespace Algolia\AlgoliaSearch\Response;

class MultiResponse extends AbstractResponse implements \Iterator, \Countable
{
    /**
     * @var int
     */
    protected $key = 0;

    /**
     * MultiResponse constructor.
     *
     * @param array $responses
     */
    public function __construct($responses)
    {
        $this->apiResponse = $responses;
    }

    /**
     * {@inheritdoc}
     */
    public function wait($requestOptions = array())
    {
        foreach ($this->apiResponse as $response) {
            $response->wait();
        }

        return $this;
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->apiResponse);
    }

    /**
     * @return array
     */
    public function current()
    {
        return $this->apiResponse[$this->key];
    }

    /**
     * Fetch next key.
     *
     * @return void
     */
    public function next()
    {
        $this->key++;
    }

    /**
     * @return int
     */
    public function key()
    {
        return $this->key;
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return isset($this->apiResponse[$this->key]);
    }

    /**
     * @return void
     */
    public function rewind()
    {
        $this->key = 0;
    }
}
