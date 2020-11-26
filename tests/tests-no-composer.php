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
    return sprintf(
        getenv('CI_BUILD_NUM') ? 'TRAVIS_php_%s_%s_%s' : 'php_%s_%s_%s',
        date('Y-M-d_H:i:s'),
        getenv('CI_BUILD_NUM') ? getenv('CI_BUILD_NUM') : get_current_user(),
        $name
    );
}
