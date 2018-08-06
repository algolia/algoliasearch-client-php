<?php

namespace Algolia\AlgoliaSearch\Tests\Integration;

use Algolia\AlgoliaSearch\Client;
use Algolia\AlgoliaSearch\Tests\SyncClient;
use PHPUnit\Framework\TestCase as PHPUitTestCase;

abstract class AlgoliaIntegrationTestCase extends PHPUitTestCase
{
    protected static $indexes = array();

    /** @var SyncClient */
    private static $client;

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();

        foreach (static::$indexes as $indexName) {
            static::getClient()->deleteIndex($indexName);
        }
    }

    public static function safeName($name)
    {
        return $name;
    }

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
            'app-id' => getenv('ALGOLIA_APP_ID'),
            'key' => getenv('ALGOLIA_API_KEY'),
            'hosts' => array(),
        );

        return new SyncClient(
            Client::create($config['app-id'], $config['key'], $config['hosts'])
        );
    }

    public $airports = array(
        array(
            "name" => "Chicago Ohare Intl",
            "city" => "Chicago",
            "country" => "United States",
            "iata_code" => "ORD",
            "zone" => "america",
            "_geoloc" => array(
                "lat" => 41.978603,
                "lng" => -87.904842
            ),
            "links_count" => 1108,
            "objectID" => "3830",
        ), array(
            "name" => "Capital Intl",
            "city" => "Beijing",
            "country" => "China",
            "iata_code" => "PEK",
            "zone" => "asia",
            "_geoloc" => array(
                "lat" => 40.080111,
                "lng" => 116.584556
            ),
            "links_count" => 1069,
            "objectID" => "3364",
        ), array(
            "name" => "Heathrow",
            "city" => "London",
            "country" => "United Kingdom",
            "iata_code" => "LHR",
            "zone" => "europe",
            "_geoloc" => array(
                "lat" => 51.4775,
                "lng" => -0.461389
            ),
            "links_count" => 1051,
            "objectID" => "507",
        ), array(
            "name" => "Charles De Gaulle",
            "city" => "Paris",
            "country" => "France",
            "iata_code" => "CDG",
            "zone" => "europe",
            "_geoloc" => array(
                "lat" => 49.012779,
                "lng" => 2.55
            ),
            "links_count" => 1041,
            "objectID" => "1382",
        ), array(
            "name" => "Los Angeles Intl",
            "city" => "Los Angeles",
            "country" => "United States",
            "iata_code" => "LAX",
            "zone" => "america",
            "_geoloc" => array(
                "lat" => 33.942536,
                "lng" => -118.408075
            ),
            "links_count" => 990,
            "objectID" => "3484",
        ),
    );
}
