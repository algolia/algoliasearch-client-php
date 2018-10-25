<?php

namespace Algolia\AlgoliaSearch\Response;

use Algolia\AlgoliaSearch\Interfaces\SearchIndexInterface;

class IndexingResponse extends AbstractResponse
{
    /**
     * @var \Algolia\AlgoliaSearch\Interfaces\SearchIndexInterface
     */
    private $index;

    public function __construct(array $apiResponse, SearchIndexInterface $index)
    {
        $this->apiResponse = $apiResponse;
        $this->index = $index;
    }

    public function wait($requestOptions = array())
    {
        if (isset($this->index)) {
            $this->index->waitTask($this->apiResponse['taskID'], $requestOptions);
            unset($this->index);
        }

        return $this;
    }
}
