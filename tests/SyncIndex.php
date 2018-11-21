<?php

namespace Algolia\AlgoliaSearch\Tests;

use Algolia\AlgoliaSearch\SearchIndex;
use Algolia\AlgoliaSearch\Response\AbstractResponse;

class SyncIndex
{
    /**
     * @var \Algolia\AlgoliaSearch\SearchIndex
     */
    private $realIndex;

    public function __construct(SearchIndex $realIndex)
    {
        $this->realIndex = $realIndex;
    }

    public function __call($name, $arguments)
    {
        $response = call_user_func_array(array($this->realIndex, $name), $arguments);

        if ($response instanceof AbstractResponse) {
            $response->wait();
        }

        return $response;
    }
}
