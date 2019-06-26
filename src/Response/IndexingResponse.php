<?php

namespace Algolia\AlgoliaSearch\Response;

use Algolia\AlgoliaSearch\SearchIndex;

final class IndexingResponse extends AbstractResponse
{
    /**
     * @var SearchIndex
     */
    private $index;

    /**
     * IndexingResponse constructor.
     *
     * @param array       $apiResponse
     * @param SearchIndex $index
     */
    public function __construct(array $apiResponse, SearchIndex $index)
    {
        $this->apiResponse = $apiResponse;
        $this->index = $index;
    }

    /**
     * {@inheritdoc}
     */
    public function wait($requestOptions = array())
    {
        if (isset($this->index)) {
            $this->index->waitTask($this->apiResponse['taskID'], $requestOptions);
            unset($this->index);
        }

        return $this;
    }
}
