<?php

require_once '../algoliasearch.php';

$client = new \AlgoliaSearch\Client('55D6OYYP5R', '125e15cb6cabe55d4b3c8f8ac7acdfde');
var_dump($client->listIndexes());
var_dump($client->getLogs());