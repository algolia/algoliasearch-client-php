<?php

namespace AlgoliaSearch;

abstract class HostsHandler implements \Iterator, \ArrayAccess
{
    private $originalHostArray;
    private $hostsArray;

    abstract protected function getPosition();
    abstract protected function setPosition($position);

    public function __construct($hostsArray)
    {
        $this->hostsArray = $hostsArray;
        $this->originalHostArray = $this->hostsArray;

        if ($this->getPosition() === null) {
            $this->setPosition(0);
            $this->shuffleHostsArray();
        }
    }

    private function shuffleHostsArray()
    {
        $firstHost = array_shift($this->hostsArray);
        shuffle($this->hostsArray);
        array_unshift($this->hostsArray, $firstHost);
    }

    public function reset()
    {
        $this->hostsArray = $this->originalHostArray;
        $this->setPosition(0);
        $this->shuffleHostsArray();
    }

    public function current()
    {
        return $this->hostsArray[$this->getPosition()];
    }

    public function next()
    {
        $this->setPosition($this->getPosition() + 1);
    }

    public function key()
    {
        return $this->getPosition();
    }

    public function valid()
    {
        return $this->getPosition() >= 0 && $this->getPosition() < count($this->hostsArray);
    }

    public function rewind()
    {
    }

    public function offsetExists($offset)
    {
        return $offset >= 0 && $offset < count($this->hostsArray);
    }

    public function offsetGet($offset)
    {
        return $this->hostsArray[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->hostsArray[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->hostsArray[$offset]);
    }

    public function toArray()
    {
        return $this->hostsArray;
    }
}
