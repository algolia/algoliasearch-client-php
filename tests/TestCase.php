<?php

namespace Algolia\AlgoliaSearch\Tests;

use Algolia\AlgoliaSearch\ApiWrapper;
use Algolia\AlgoliaSearch\Client;
use Http\Message\MessageFactory\GuzzleMessageFactory;
use PHPUnit\Framework\TestCase as PHPUitTestCase;

abstract class TestCase extends PHPUitTestCase
{
    public function safeName($name)
    {
        return $name;
    }

    protected function getClient()
    {
        $wrapper = new ApiWrapper(
            getenv('ALGOLIA_APP_ID'),
            getenv('ALGOLIA_API_KEY'),
            new \Http\Adapter\Guzzle6\Client(),
            new GuzzleMessageFactory()
        );

        return new Client($wrapper);
    }
}
