<?php

namespace Algolia\AlgoliaSearch\Tests;

use Algolia\AlgoliaSearch\ApiWrapper;
use Algolia\AlgoliaSearch\Client;
use Algolia\AlgoliaSearch\ClusterHosts;
use Http\Message\MessageFactory\GuzzleMessageFactory;
use Http\Message\UriFactory\GuzzleUriFactory;
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
            ClusterHosts::createFromAppId(getenv('ALGOLIA_APP_ID')),
            new \Http\Adapter\Guzzle6\Client(),
            new GuzzleMessageFactory(),
            new GuzzleUriFactory()
        );

        return new Client($wrapper);
    }
}
