<?php

require '../vendor/autoload.php';

$client = Algolia\AlgoliaSearch\SearchClient::create("I2UB5B7IZB", getenv('ALGOLIA_API_KEY'));

$indices = $client->listIndices();

foreach(array_chunk($indices['items'], 100) as $chunk) {
    $ops = array();
    foreach($chunk as $index) {
        array_push($ops, [
            'indexName' => $index['name'],
            'action' => 'delete',
        ]);
    }

    $client->multipleBatch($ops);
}
