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
        return Client::create(
            getenv('ALGOLIA_APP_ID'),
            getenv('ALGOLIA_API_KEY')
        );
    }
}
