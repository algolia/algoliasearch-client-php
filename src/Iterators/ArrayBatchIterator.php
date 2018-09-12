<?php

namespace Algolia\AlgoliaSearch\Iterators;

use Traversable;

class ArrayBatchIterator extends AbstractBatchIterator
{
    /**
     * The items to be sliced into batches.
     *
     * @var array
     */
    private $items;

    /**
     * The number of items per batch.
     *
     * @var int
     */
    private $itemsPerBatch;

    /**
     * Create a new ArrayBatchIterator.
     *
     * @param mixed $items
     * @param int $itemsPerBatch
     */
    public function __construct($items, $itemsPerBatch = 200)
    {
        $this->items = $this->getArrayableItems($items);
        $this->itemsPerBatch = (int)$itemsPerBatch;
    }

    /**
     * {@inheritdoc}
     */
    public function getBatch()
    {
        $offset = $this->currentBatchNumber * $this->itemsPerBatch;

        $length = $offset + $this->itemsPerBatch;

        return array_slice($this->items, $offset, $length);
    }

    /**
     * Results array of items from the provided value.
     *
     * @param  mixed $items
     * @return array
     */
    private function getArrayableItems($items)
    {
        if (! is_array($items)) {
            $items = (array)$items;
        } elseif ($items instanceof Traversable) {
            $items = iterator_to_array($items);
        }
        return array_values((array)$items);
    }
}
