<?php

namespace Algolia\AlgoliaSearch\Tests\Cts;

use Algolia\AlgoliaSearch\Config\SearchConfig;
use Algolia\AlgoliaSearch\SearchClient;
use Algolia\AlgoliaSearch\Tests\SyncClient;
use Faker\Factory;

class TestHelper
{
    protected static $indexes = array();

    private static $instance;

    private static $environmentVariables = array(
        'ALGOLIA_APPLICATION_ID_1',
        'ALGOLIA_ADMIN_KEY_1',
//        'ALGOLIA_SEARCH_KEY_1',
//        'ALGOLIA_APPLICATION_ID_2',
//        'ALGOLIA_ADMIN_KEY_2',
//        'ALGOLIA_APPLICATION_ID_MCM',
//        'ALGOLIA_ADMIN_KEY_MCM',
    );

    /** @var SyncClient */
    private static $client;

    public static $employees = array(
        array('company' => 'Algolia', 'name' => 'Julien Lemoine', 'objectID' => 'julien-lemoine'),
        array('company' => 'Algolia', 'name' => 'Nicolas Dessaigne', 'objectID' => 'nicolas-dessaigne'),
        array('company' => 'Amazon', 'name' => 'Jeff Bezos'),
        array('company' => 'Apple', 'name' => 'Steve Jobs'),
        array('company' => 'Apple', 'name' => 'Steve Wozniak'),
        array('company' => 'Arista Networks', 'name' => 'Jayshree Ullal'),
        array('company' => 'Google', 'name' => 'Larry Page'),
        array('company' => 'Google', 'name' => 'Rob Pike'),
        array('company' => 'Google', 'name' => 'Serguey Brin'),
        array('company' => 'Microsoft', 'name' => 'Bill Gates'),
        array('company' => 'SpaceX', 'name' => 'Elon Musk'),
        array('company' => 'Tesla', 'name' => 'Elon Musk'),
        array('company' => 'Yahoo', 'name' => 'Marissa Mayer'),
    );

    public static $consoles = array(
        array('console' => 'Sony PlayStation <PLAYSTATIONVERSION>'),
        array('console' => 'Nintendo Switch'),
        array('console' => 'Nintendo Wii U'),
        array('console' => 'Nintendo Game Boy Advance'),
        array('console' => 'Microsoft Xbox'),
        array('console' => 'Microsoft Xbox 360'),
        array('console' => 'Microsoft Xbox One'),
    );

    public static $smartphones = array(
        array('objectID' => 'iphone_7', 'brand' => 'Apple', 'model' => '7'),
        array('objectID' => 'iphone_8', 'brand' => 'Apple', 'model' => '7'),
        array('objectID' => 'iphone_x', 'brand' => 'Apple', 'model' => '7'),
        array('objectID' => 'one_plus_one', 'brand' => 'OnePlus', 'model' => 'One'),
        array('objectID' => 'one_plus_two', 'brand' => 'OnePlus', 'model' => 'Two'),
    );

    public static $figures = array(
        array('objectID' => 'one', 'key' => 'value'),
        array('objectID' => 'two', 'key' => 'value'),
        array('objectID' => 'three', 'key' => 'value'),
        array('objectID' => 'four', 'key' => 'value'),
        array('objectID' => 'five', 'key' => 'value'),
    );

    public static $batch = array(
        array('action' => 'addObject', 'body' => array('objectID' => 'zero', 'key' => 'value')),
        array('action' => 'updateObject', 'body' => array('objectID' => 'one', 'k' => 'v')),
        array('action' => 'partialUpdateObject', 'body' => array('objectID' => 'two', 'k' => 'v')),
        array('action' => 'partialUpdateObject', 'body' =>array('objectID' => 'two_bis', 'key' => 'value')),
        array('action' => 'partialUpdateObjectNoCreate', 'body' => array('objectID' => 'three', 'k' => 'v')),
        array('action' => 'deleteObject', 'body' => array('objectID' => 'four')),
    );

    public static $figuresAfterBatch = array(
        array('objectID' => 'zero', 'key' => 'value'),
        array('objectID' => 'one', 'k' => 'v'),
        array('objectID' => 'two', 'key' => 'value', 'k' => 'v'),
        array('objectID' => 'two_bis', 'key' => 'value'),
        array('objectID' => 'three', 'key' => 'value', 'k' => 'v'),
        array('objectID' => 'five', 'key' => 'value'),
    );

    /**
     * @throws \Exception
     */
    public static function checkEnvironmentVariables()
    {
        foreach (self::$environmentVariables as $name) {
            if (!getenv($name)) {
                throw new \Exception("$name must be defined.");
            }
        }
    }

    public static function getTestIndexName($name)
    {
        return sprintf(
            'php_%s_%s_%s',
            date('Y-M-d_H:i:s'),
            self::getInstance(),
            $name
        );
    }

    public static function getTestUserName($name)
    {
        return sprintf(
            'php-%s-%s-%s',
            date('Y-m-d-H-i-s'),
            self::getInstance(),
            $name
        );
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = getenv('CI_BUILD_NUM') ? getenv('CI_BUILD_NUM') : get_current_user();
        }

        return self::$instance;
    }

    /**
     * @return \Algolia\AlgoliaSearch\SearchClient
     */
    public static function getClient()
    {
        if (!self::$client) {
            self::$client = self::newClient();
        }

        return self::$client;
    }

    protected static function newClient($config = array())
    {
        $config += array(
            'appId' => getenv('ALGOLIA_APPLICATION_ID_1'),
            'apiKey' => getenv('ALGOLIA_ADMIN_KEY_1'),
        );

        return new SyncClient(
            SearchClient::createWithConfig(new SearchConfig($config))
        );
    }

    public static function createRecord($objectID = false)
    {
        $faker = Factory::create();
        $record = array('name' => $faker->name);

        if ($objectID === null) {
            $record['objectID'] = uniqid('php_client_', true);
        } elseif ($objectID !== false) {
            $record['objectID'] = $objectID;
        }

        return $record;
    }

    public static function formatRule($rule)
    {
        if (isset($rule['_metadata'])) {
            unset($rule['_metadata']);
        }

        if (isset($rule['_highlightResult'])) {
            unset($rule['_highlightResult']);
        }

        return $rule;
    }
}
