<?php

use Algolia\AlgoliaSearch\Algolia;
use Algolia\AlgoliaSearch\Http\CurlHttpClient;

require_once __DIR__.'/../vendor/autoload.php';

Algolia::setHttpClient(new CurlHttpClient());
