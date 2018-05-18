<?php

namespace Algolia\AlgoliaSearch\Tests;

use Algolia\AlgoliaSearch\Index;

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

        if (isset($response['taskID'])) {
            $this->realIndex->waitTask($response['taskID']);
        }

        return $response;
    }
}
