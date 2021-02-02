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
        'ALGOLIA_SEARCH_KEY_1',
        'ALGOLIA_APPLICATION_ID_2',
        'ALGOLIA_ADMIN_KEY_2',
//        'ALGOLIA_APPLICATION_ID_MCM',
//        'ALGOLIA_ADMIN_KEY_MCM',
    );

    /** @var SearchClient[] */
    private static $client = array();

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
     * @param array $config
     *
     * @return SearchClient
     */
    public static function getClient($config = array())
    {
        $config += array(
            'appId' => getenv('ALGOLIA_APPLICATION_ID_1'),
            'apiKey' => getenv('ALGOLIA_ADMIN_KEY_1'),
        );

        $idFromApiKey = $config['appId'] . substr($config['apiKey'], 0, 5);

        if (!isset(self::$client[$idFromApiKey])) {
            self::$client[$idFromApiKey] = SearchClient::createWithConfig(new SearchConfig($config));
        }

        return self::$client[$idFromApiKey];
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
