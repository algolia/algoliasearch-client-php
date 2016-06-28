<?php

require_once '../algoliasearch.php';

$client = new \AlgoliaSearch\Client('<YOUR_APP_ID>', '<YOUR_API_KEY>');
var_dump($client->listIndexes());
var_dump($client->getLogs());
