<?php

namespace Algolia\AlgoliaSearch\Response;

abstract class AbstractResponse implements \ArrayAccess
{
    /**
     * @var array Full response from Algolia API
     */
    protected $apiResponse;

    /**
     * @param array $requestOptions
     *
     * @return $this
     */
    abstract public function wait($requestOptions = array());

    /**
     * @return array The actual response from Algolia API
     */
    public function getBody()
    {
        return $this->apiResponse;
    }

    /**
     * @param int $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->apiResponse[$offset]);
    }

    /**
     * @param int $offset
     *
     * @return AbstractResponse
     */
    public function offsetGet($offset)
    {
        return $this->apiResponse[$offset];
    }

    /**
     * @param int              $offset
     * @param AbstractResponse $value
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->apiResponse[$offset] = $value;
    }

    /**
     * @param int $offset
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->apiResponse[$offset]);
    }
}
