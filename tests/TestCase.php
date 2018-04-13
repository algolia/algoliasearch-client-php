<?php

namespace Algolia\AlgoliaSearch\Tests;

use Algolia\AlgoliaSearch\Client;
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
