<?php

require_once '../algoliasearch.php';

$client = new \AlgoliaSearch\Client('<YOUR_APP_ID>', '<YOUR_API_KEY>'); // .env is suggested for storing sensitive API/APP keys.
var_dump($client->listIndexes());
var_dump($client->getLogs());
