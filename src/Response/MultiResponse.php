<?php

namespace Algolia\AlgoliaSearch\Response;

final class MultiResponse extends AbstractResponse implements \Iterator, \Countable
{
    /**
     * @var int
     */
    protected $key = 0;

    /**
     * MultiResponse constructor.
     *
     * @param array<int, AbstractResponse> $responses
     */
    public function __construct($responses)
    {
        $this->apiResponse = $responses;
    }

    /**
     * @param array $requestOptions
     *
     * @return $this
     */
    public function wait($requestOptions = array())
    {
        foreach ($this->apiResponse as $response) {
            $response->wait();
        }

        return $this;
    }

    /**
     * @return int|void
     */
    public function count()
    {
        return count($this->apiResponse);
    }

    /**
     * {@inheritdoc}
     *
     * @return AbstractResponse
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
     * @return int|mixed
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
     * Rewind API key.
     *
     * @return void
     */
    public function rewind()
    {
        $this->key = 0;
    }
}
