<?php

namespace Algolia\AlgoliaSearch\Tests\Integration;

class SettingsTest extends AlgoliaIntegrationTestCase
{
    private $settings = array(
        'searchableAttributes' =>	array("attribute1", "attribute2", "attribute3", "ordered(attribute4)", "unordered(attribute5)" ),
        'attributesForFaceting' =>	array( "attribute1", "filterOnly(attribute2)", "searchable(attribute3)" ),
        'unretrievableAttributes'=>	array( "attribute1", "attribute2" ),
        'attributesToRetrieve' =>	array( "attribute3", "attribute4" ),
        'ranking' => array( "asc(attribute1)", "desc(attribute2)", "attribute", "custom", "exact", "filters", "geo", "proximity", "typo", "words" ),
        'customRanking' =>	array( "asc(attribute1)", "desc(attribute1)" ),
        'replicas' => array("main_replica1", "main_replica2" ),
        'maxValuesPerFacet' =>	100,
        'sortFacetValuesBy'	=> "count",
        'attributesToHighlight'	=> array( "attribute1", "attribute2" ),
        'attributesToSnippet'=> array( "attribute1:10", "attribute2:8" ),
        'highlightPreTag' => "<strong>",
        'highlightPostTag'=>"</strong>",
        'snippetEllipsisText'=>" and so on.",
        'restrictHighlightAndSnippetArrays' =>	true,
        'hitsPerPage'	=> 42,
        'paginationLimitedTo'=> 43,
        'minWordSizefor1Typo' => 2,
        'minWordSizefor2Typos'=> 6,
        'typoTolerance' => 	'false',
        'allowTyposOnNumericTokens' =>	false,
        'ignorePlurals'=> 	true,
        'disableTypoToleranceOnAttributes' =>	array( "attribute1", "attribute2" ),
        'disableTypoToleranceOnWords' =>  array( "word1", "word2" ),
        'separatorsToIndex' =>	"()array()(",
        'queryType' =>	"prefixNone",
        'removeWordsIfNoResults' =>	"allOptional",
        'advancedSyntax' =>	true,
        'optionalWords' =>	array( "word1", "word2" ),
        'removeStopWords'=> true,
        'disablePrefixOnAttributes' =>	array( "attribute1", "attribute2" ),
        'disableExactOnAttributes'	=> array( "attribute1", "attribute2" ),
        'exactOnSingleWordQuery' => "word",
        'enableRules' => false,
        'numericAttributesForFiltering'	=> array( "attribute1", "attribute2" ),
        'allowCompressionOfIntegerArray' => true,
        'attributeForDistinct'	=> "attribute1",
        'distinct' =>	2,
        'replaceSynonymsInHighlight' =>	false,
        'minProximity' =>	7,
        'responseFields' => 	array( "hits", "hitsPerPage" ),
        'maxFacetHits' =>	100,
        'camelCaseAttributes'=> array( "attribute1", "attribute2" ),
        'decompoundedAttributes' => 	array("de"=> array("attribute1", "attribute2"), "fi"=> array("attribute3") ),
        'keepDiacriticsOnCharacters' => "øé",


    );

    protected function setUp()
    {
        parent::setUp();

        if (!isset(static::$indexes['main'])){
            static::$indexes['main'] = self::safeName('settings-mgmt');
        }

    }
    public function testSettingsCanBeUpdatedAndRetrieved()
    {
        /**  Instantiate the client and index setting */
        $index = static::getClient()->initIndex(static::$indexes['main']);
        $responses = array();

        /** Add one record to create the index with saveObject */
        $object = array( 'objectID'=> 1,'name'=>'foo');
        $index->saveObject($object);

        /** Set the settings with $settings with setSettings */
        $responses [] = $index->setSettings($this->settings);

        /** Wait all collected task to terminate */
        foreach ($responses as $r) {
            $r->wait();
        }
        /** Get the settings with getSettings  */
        $retrievedSettings = $index->getSettings();
        self::assertArraySubset($this->settings, $retrievedSettings);

        /** Set the settings with the following parameters with setSettings */
        $responses[] = $index->setSettings(array('typoTolerance' =>	"min", 'ignorePlurals' => array("en", "fr"), 'removeStopWords'=>array("en", "fr"), 'distinct' =>true));

        /** Wait all collected task to terminate */
        foreach ($responses as $r) {
            $r->wait();
        }
        /**  Get the settings with getSettings after update */

        $this->settings['typoTolerance'] = true ;
        $this->settings['ignorePlurals'] = array("en", "fr") ;
        $this->settings['removeStopWords'] = array("en", "fr") ;
        $this->settings['distinct'] = true ;

        $retrievedSettings = $index->getSettings();
        self::assertArraySubset($this->settings, $retrievedSettings);
    }

    /**
     * @depends testSettingsCanBeUpdatedAndRetrieved
     */
    public function testSettingsWithReplicas()
    {
        $replica1 = self::safeName('settings-mgmt_REPLICA');

        try {
            static::getClient()->initIndex($replica1)->delete();
        } catch (\Exception $e) {
        }
        $index = static::getClient()->initIndex(static::$indexes['main']);
        $replica = static::getClient()->initIndex($replica1);

        $settingsWithReplicas = array_merge($this->settings, array(
            'replicas' => array($replica1),
            'hitsPerPage' => 16,
            'minWordSizefor2Typos' => 9,
            'paginationLimitedTo' => 885,
        ));

        // Assert that settings are NOT forwarded by default
        $index->setSettings($settingsWithReplicas);
        $retrievedPrimarySettings = $index->getSettings();
        $retrievedReplicaSettings = $replica->getSettings();
        $this->assertNotEquals($retrievedPrimarySettings['hitsPerPage'], $retrievedReplicaSettings['hitsPerPage']);

        // If I set forwardToReplicas to true by default, it should work
        /** @var \Algolia\AlgoliaSearch\SearchIndex $index */
        $index = self::newClient(array('defaultForwardToReplicas' => true))
            ->initIndex(static::$indexes['main']);
        $index->setSettings($settingsWithReplicas);
        $retrievedPrimarySettings = $index->getSettings();
        $retrievedReplicaSettings = $replica->getSettings();
        $this->assertEquals($retrievedPrimarySettings['hitsPerPage'], $retrievedReplicaSettings['hitsPerPage']);

        // If the new default is true, I can still override it
        $formula = array('customRanking' => array('asc(something)'));
        $index->setSettings($formula, array('forwardToReplicas' => false));
        $retrievedSettings = $replica->getSettings();
        $this->assertEquals(null, $retrievedSettings['customRanking']);
    }
}
