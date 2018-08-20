<?php

namespace Algolia\AlgoliaSearch\Iterators;

abstract class AbstractBatchIterator implements \Iterator
{
    protected $batch;

    protected $currentBatchNumber = 0;

    abstract public function getBatch();

    public function current()
    {
        return $this->batch;
    }

    public function next()
    {
        $this->currentBatchNumber++;
        $this->batch = $this->getBatch();
    }

    public function key()
    {
        return $this->currentBatchNumber;
    }

    public function valid()
    {
        if (0 === $this->currentBatchNumber) {
            $this->batch = $this->getBatch();
        }

        return (is_array($this->batch) || $this->batch instanceof \JsonSerializable)
            && !empty($this->batch);
    }

    public function rewind()
    {
        $this->currentBatchNumber = 0;
        $this->batch = null;
    }
 }
