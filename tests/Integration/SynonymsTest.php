<?php

namespace Algolia\AlgoliaSearch\Tests\Integration;

use Algolia\AlgoliaSearch\Config\SearchConfig;
use Algolia\AlgoliaSearch\RequestOptions\RequestOptionsFactory;

class SynonymsTest extends AlgoliaIntegrationTestCase
{
    private $caliSyn = array(
        'objectID' => 'cali',
        'type' => 'synonym',
        'synonyms' => array('Los Angeles', 'LA', 'Venice'),
    );

    private $pekingSyn = array(
        'objectID' => 'china',
        'type' => 'synonym',
        'synonyms' => array('Beijing', 'Peking'),
    );

    private $anotherSyn = array(
        'objectID' => 'city',
        'type' => 'synonym',
        'synonyms' => array('city', 'town', 'village'),
    );

    protected function setUp()
    {
        parent::setUp();

        if (!isset(static::$indexes['main'])) {
            static::$indexes['main'] = self::safeName('synomyms-mgmt');
        }
    }

    public function testSynonymsCanBeSavedAndRetrieved()
    {
        $index = static::getClient()->initIndex(static::$indexes['main']);

        $index->saveObjects($this->airports);

        $index->saveSynonym($this->pekingSyn);

        $index->saveSynonyms(array($this->caliSyn));

        $this->assertArraySubset($this->pekingSyn, $index->getSynonym('china'));
        $this->assertArraySubset($this->caliSyn, $index->getSynonym('cali'));

        $index->deleteSynonym('china');

        $res = $index->searchSynonyms('');
        $this->assertArraySubset(array('nbHits' => 1), $res);

        $index->replaceAllSynonyms(array($this->anotherSyn));
        $res = $index->searchSynonyms('');
        $this->assertArraySubset(array('nbHits' => 1), $res);
        $this->assertArraySubset($this->anotherSyn, $res['hits'][0]);

        $index->clearSynonyms();
        $res = $index->searchSynonyms('');
        $this->assertArraySubset(array('nbHits' => 0), $res);
    }

    public function testClearExistingSynonymsOption()
    {
        $index = static::getClient()->initIndex(static::$indexes['main']);

        $index->saveObjects($this->airports);

        $index->saveSynonym($this->pekingSyn);

        $index->saveSynonyms(array($this->caliSyn));

        $this->assertArraySubset($this->pekingSyn, $index->getSynonym('china'));
        $this->assertArraySubset($this->caliSyn, $index->getSynonym('cali'));
        $result = $index->searchSynonyms('');
        $this->assertCount(2, $result['hits']);

        $clearExistingSynonyms = array('clearExistingSynonyms' => true);
        $factory = new RequestOptionsFactory(
            new SearchConfig(array(
                'appId' => getenv('ALGOLIA_APP_ID'),
                'apiKey' => getenv('ALGOLIA_API_KEY'),
            ))
        );
        $requestOptions = $factory->create($clearExistingSynonyms);
        $index->saveSynonyms(array($this->anotherSyn), $requestOptions);

        $this->assertArraySubset($this->anotherSyn, $index->getSynonym('city'));
        $this->assertCount(1, $index->searchSynonyms('')['hits']);

        $index->saveSynonyms(array($this->caliSyn), array('clearExistingSynonyms' => true));

        $this->assertArraySubset($this->caliSyn, $index->getSynonym('cali'));
        $result = $index->searchSynonyms('');
        $this->assertCount(1, $result['hits']);
    }

    public function testBrowseSynonyms()
    {
        $index = static::getClient()->initIndex(static::$indexes['main']);

        $index->saveObject($this->airports[0]);

        $index->replaceAllSynonyms(array($this->caliSyn, $this->pekingSyn, $this->anotherSyn));

        $previousObjectID = '';
        $i = 0;
        $iterator = $index->browseSynonyms(array('hitsPerPage' => 1));
        foreach ($iterator as $synonym) {
            $this->assertArraySubset(array('type' => 'synonym'), $synonym);
            $this->assertNotEquals($synonym['objectID'], $previousObjectID);
            $previousObjectID = $synonym['objectID'];
            $i++;
        }

        $this->assertEquals(3, $i);
    }
}
