<?php
require 'vendor/autoload.php';


$fileBasedCache = new \AlgoliaSearch\FileFailingHostsCache();
$fileBasedCache->flushFailingHostsCache();
$client = new \AlgoliaSearch\Client(
	getenv('ALGOLIA_APPLICATION_ID'),
	getenv('ALGOLIA_API_KEY'),
	array(
		'APP_ID_1' . '.algolia.biz', // .biz will always fail to resolve
		getenv('ALGOLIA_APPLICATION_ID') . '.algolia.biz',
		getenv('ALGOLIA_APPLICATION_ID') . '.algolia.net'
	),
	array(
		\AlgoliaSearch\Client::FAILING_HOSTS_CACHE => $fileBasedCache
	)
);

$start = microtime(true);
$client->listIndexes();
$end = round((microtime(true) - $start)*1000);
echo '[FileFailingHostsCache] First call processing time:' . $end . PHP_EOL;


$start = microtime(true);
$client->listIndexes();
$end = round((microtime(true) - $start)*1000);
echo '[FileFailingHostsCache] Second call processing time:' . $end . PHP_EOL;

$client = new \AlgoliaSearch\Client(
	getenv('ALGOLIA_APPLICATION_ID'),
	getenv('ALGOLIA_API_KEY'),
	array(
		'APP_ID_1' . '.algolia.biz', // .biz will always fail to resolve
		getenv('ALGOLIA_APPLICATION_ID') . '.algolia.biz',
		getenv('ALGOLIA_APPLICATION_ID') . '.algolia.net'
	),
	array(
		\AlgoliaSearch\Client::FAILING_HOSTS_CACHE => new \AlgoliaSearch\InMemoryFailingHostsCache()
	)
);

$start = microtime(true);
$client->listIndexes();
$end = round((microtime(true) - $start)*1000);
echo '[InMemoryFailingHostsCache] First call processing time:' . $end . PHP_EOL;


$start = microtime(true);
$client->listIndexes();
$end = round((microtime(true) - $start)*1000);
echo '[InMemoryFailingHostsCache] Second call processing time:' . $end . PHP_EOL;
