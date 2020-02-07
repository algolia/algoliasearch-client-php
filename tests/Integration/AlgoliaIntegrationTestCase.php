<?php

namespace Algolia\AlgoliaSearch\Tests\Integration;

use Algolia\AlgoliaSearch\Config\SearchConfig;
use Algolia\AlgoliaSearch\SearchClient;
use Algolia\AlgoliaSearch\Tests\SyncClient;
use PHPUnit\Framework\TestCase as PHPUitTestCase;

abstract class AlgoliaIntegrationTestCase extends PHPUitTestCase
{
    protected static $indexes = array();

    private static $instance;

    /** @var SyncClient */
    private static $client;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        static::$indexes = array();
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();

        foreach (static::$indexes as $indexName) {
            static::getClient()->initIndex($indexName)->delete();
        }
    }

    public static function safeName($name)
    {
        if (!self::$instance) {
            self::$instance = getenv('TRAVIS') ? getenv('TRAVIS_JOB_NUMBER') : get_current_user();
        }

        return sprintf(
            'php_%s_%s_%s',
            date('Y-M-d_H:i:s'),
            self::$instance,
            $name
        );
    }

    public static function safeUserName($name)
    {
        if (!self::$instance) {
            self::$instance = getenv('TRAVIS') ? getenv('TRAVIS_JOB_NUMBER') : get_current_user();
        }

        return sprintf(
            'php-%s-%s-%s',
            date('Y-m-d-H-i-s'),
            self::$instance,
            $name
        );
    }

    /**
     * @return \Algolia\AlgoliaSearch\SearchClient
     */
    protected static function getClient()
    {
        if (!self::$client) {
            self::$client = self::newClient();
        }

        return self::$client;
    }

    protected static function newClient($config = array())
    {
        $config += array(
            'appId' => getenv('ALGOLIA_APP_ID'),
            'apiKey' => getenv('ALGOLIA_API_KEY'),
        );

        return new SyncClient(
            SearchClient::createWithConfig(new SearchConfig($config))
        );
    }

    public $airports = array(
        array(
            'name' => 'Chicago Ohare Intl',
            'city' => 'Chicago',
            'country' => 'United States',
            'iata_code' => 'ORD',
            'zone' => 'america',
            '_geoloc' => array(
                'lat' => 41.978603,
                'lng' => -87.904842,
            ),
            'links_count' => 1108,
            'objectID' => '3830',
        ), array(
            'name' => 'Capital Intl',
            'city' => 'Beijing',
            'country' => 'China',
            'iata_code' => 'PEK',
            'zone' => 'asia',
            '_geoloc' => array(
                'lat' => 40.080111,
                'lng' => 116.584556,
            ),
            'links_count' => 1069,
            'objectID' => '3364',
        ), array(
            'name' => 'Heathrow',
            'city' => 'London',
            'country' => 'United Kingdom',
            'iata_code' => 'LHR',
            'zone' => 'europe',
            '_geoloc' => array(
                'lat' => 51.4775,
                'lng' => -0.461389,
            ),
            'links_count' => 1051,
            'objectID' => '507',
        ), array(
            'name' => 'Charles De Gaulle',
            'city' => 'Paris',
            'country' => 'France',
            'iata_code' => 'CDG',
            'zone' => 'europe',
            '_geoloc' => array(
                'lat' => 49.012779,
                'lng' => 2.55,
            ),
            'links_count' => 1041,
            'objectID' => '1382',
        ), array(
            'name' => 'Los Angeles Intl',
            'city' => 'Los Angeles',
            'country' => 'United States',
            'iata_code' => 'LAX',
            'zone' => 'america',
            '_geoloc' => array(
                'lat' => 33.942536,
                'lng' => -118.408075,
            ),
            'links_count' => 990,
            'objectID' => '3484',
        ),
    );

    public $companies = array(
        array('company' => 'Algolia', 'name' => 'Julien Lemoine', 'objectID' => 'julien-lemoine'),
        array('company' => 'Algolia', 'name' => 'Nicolas Dessaigne', 'objectID' => 'nicolas-dessaigne'),
        array('company' => 'Amazon', 'name' => 'Jeff Bezos', 'objectID' => '1234'),
        array('company' => 'Apple', 'name' => 'Steve Jobs', 'objectID' => '1235'),
        array('company' => 'Apple', 'name' => 'Steve Wozniak', 'objectID' => '1236'),
        array('company' => 'Arista Networks', 'name' => 'Jayshree Ullal', 'objectID' => '1237'),
        array('company' => 'Google', 'name' => 'Larry Page', 'objectID' => '1238'),
        array('company' => 'Google', 'name' => 'Rob Pike', 'objectID' => '1239'),
        array('company' => 'Google', 'name' => 'Serguey Brin', 'objectID' => '1240'),
        array('company' => 'Microsoft', 'name' => 'Bill Gates', 'objectID' => '1241'),
        array('company' => 'SpaceX', 'name' => 'Elon Musk', 'objectID' => '1242'),
        array('company' => 'Tesla', 'name' => 'Elon Musk', 'objectID' => '1243'),
        array('company' => 'Yahoo', 'name' => 'Marissa Mayer', 'objectID' => '1244'),
    );
}
