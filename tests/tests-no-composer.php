<?php

passthru(dirname(__DIR__).'/bin/install-dependencies-without-composer');

require_once dirname(__DIR__).'/autoload.php';

$client = \Algolia\AlgoliaSearch\SearchClient::get();

$indexName = safeName('client-tests-co-composer');
$index = $client->initIndex($indexName);

$index->setSettings(array('hitsPerPage' => 30))->wait();
$index->delete()->wait();

function safeName($name)
{
    if (getenv('CI_BUILD_NUM')) {
        return sprintf('php_%s_%s', $name, getenv('CI_BUILD_NUM'));
    }

    return $name;
}
