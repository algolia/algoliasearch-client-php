<?php

passthru(dirname(__DIR__).'/bin/install-dependencies-without-composer');

require_once dirname(__DIR__).'/autoload.php';

$client = \Algolia\AlgoliaSearch\SearchClient::get();

$indexName = safeName('client-tests-co-composer');
$index = $client->initIndex($indexName)->setSettings(array('hitsPerPage' => 30))->wait();

$client->deleteIndex($indexName)->wait();

function safeName($name)
{
    if (getenv('TRAVIS')) {
        return sprintf('TRAVIS_php_%s_%s', $name, getenv('TRAVIS_JOB_NUMBER'));
    }

    return $name;
}
