<?php

namespace AlgoliaSearch\Tests;

use AlgoliaSearch\AlgoliaException;
use AlgoliaSearch\Client;

class AccessTest extends AlgoliaSearchTestCase
{
    public function testHTTPAccess() {
      $client = new Client(getenv('ALGOLIA_APPLICATION_ID'), getenv('ALGOLIA_API_KEY'), array("http://" . getenv('ALGOLIA_APPLICATION_ID') . "-1.algolia.io"));
      $client->isAlive();
    }

    public function testHTTPSAccess() {
      $client = new Client(getenv('ALGOLIA_APPLICATION_ID'), getenv('ALGOLIA_API_KEY'), array("https://" . getenv('ALGOLIA_APPLICATION_ID') . "-1.algolia.io"));
      $client->isAlive();
    }

}
