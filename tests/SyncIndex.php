<?php

namespace Algolia\AlgoliaSearch\Tests;

use Algolia\AlgoliaSearch\Index;
use Algolia\AlgoliaSearch\Response\AbstractResponse;

class SyncIndex
{
    /**
     * @var \Algolia\AlgoliaSearch\Index
     */
    private $realIndex;

    public function __construct(Index $realIndex)
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
