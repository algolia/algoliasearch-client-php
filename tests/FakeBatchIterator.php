<?php

namespace Algolia\AlgoliaSearch\Tests;

use Algolia\AlgoliaSearch\Iterators\AbstractBatchIterator;

class FakeBatchIterator extends AbstractBatchIterator
{
    private $data = array();
    private $perPage;

    public function __construct()
    {
        foreach (range(1000, 1100) as $id) {
            $this->data[] = array(
                'objectID' => $id,
                'someAttribute' => 'text'
            );
        }
        $this->perPage = 10;
    }

    public function getBatch()
    {
        $start = $this->perPage * $this->currentBatchNumber;

        return array_slice($this->data, $start, $this->perPage);
    }
}
