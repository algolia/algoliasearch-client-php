<?php

namespace Algolia\AlgoliaSearch\Tests\Cts\Integration;

use Algolia\AlgoliaSearch\Tests\Cts\TestHelper;
use PHPUnit\Framework\TestCase as PHPUitTestCase;

abstract class BaseTest extends PHPUitTestCase
{
    protected static $indexes = array();

    public static function setUpBeforeClass()
    {
        try {
            TestHelper::checkEnvironmentVariables();
        } catch (\Exception $e) {
            echo $e->getMessage() . "\n";
            exit(255);
        }

        parent::setUpBeforeClass();
        static::$indexes = array();
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();

        foreach (static::$indexes as $indexName) {
            TestHelper::getClient()->initIndex($indexName)->delete();
        }
    }
}
