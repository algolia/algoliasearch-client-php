<?php

namespace Algolia\AlgoliaSearch\Response;

use Algolia\AlgoliaSearch\Exceptions\CannotWaitException;
use Algolia\AlgoliaSearch\Interfaces\IndexInterface;

class IndexingResponse extends AbstractResponse
{
    /**
     * @var \Algolia\AlgoliaSearch\Interfaces\IndexInterface
     */
    private $index;

    public function __construct(array $apiResponse, IndexInterface $index)
    {
        $this->apiResponse = $apiResponse;
        $this->index = $index;
    }

    public function wait($requestOptions = array())
    {
        if ($this->index) {
            $this->index->waitTask($this->apiResponse['taskId'], $requestOptions);
            unset($this->index);
        }

        return $this;
    }
}
