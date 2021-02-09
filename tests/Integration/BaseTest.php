<?php

namespace Algolia\AlgoliaSearch\Tests\Integration;

use Algolia\AlgoliaSearch\Tests\TestHelper;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

abstract class BaseTest extends PHPUnitTestCase
{
    protected static $indexes = array();

    public static function setUpBeforeClass()
    {
        try {
            TestHelper::checkEnvironmentVariables();
        } catch (\Exception $e) {
            echo $e->getMessage()."\n";
            return;
        }

        parent::setUpBeforeClass();
        static::$indexes = array();
    }
}
