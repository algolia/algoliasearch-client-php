<?php

namespace Algolia\AlgoliaSearch\Response;

abstract class AbstractResponse implements \ArrayAccess
{
    /**
     * @var array Full response from Algolia API
     */
    protected $apiResponse;

    abstract public function wait($requestOptions = []);

    /**
     * @return array The actual response from Algolia API
     */
    public function getBody()
    {
        return $this->apiResponse;
    }

    /**
     * {@inheritdoc}
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return isset($this->apiResponse[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->apiResponse[$offset];
    }

    /**
     * {@inheritdoc}
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        $this->apiResponse[$offset] = $value;
    }

    /**
     * {@inheritdoc}
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        unset($this->apiResponse[$offset]);
    }
}
