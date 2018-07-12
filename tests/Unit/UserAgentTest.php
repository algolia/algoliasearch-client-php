<?php

namespace Algolia\AlgoliaSearch\Tests\Unit;

use Algolia\AlgoliaSearch\Support\Config;
use PHPUnit\Framework\TestCase;

class UserAgentTest extends TestCase
{
    private $default;

    public function setUp()
    {
        $this->default = 'PHP ('.str_replace(PHP_EXTRA_VERSION, '', PHP_VERSION).'); ';
        if (defined('HHVM_VERSION')) {
            $this->default .= '; HHVM ('.HHVM_VERSION.')';
        }
        $this->default .= 'Algolia for PHP ('.Config::VERSION.')';
    }

    public function testDefaultUserAgent()
    {
        $this->assertRegExp('/^PHP \(\d+\.\d+\.\d+\); Algolia for PHP \(\d+\.\d+\.\d+\)$/', Config::getUserAgent());

        $this->assertEquals($this->default, Config::getUserAgent());
    }

    public function testWithCustomUserAgent()
    {
        $segment1 = 'Framework blah integration';
        $version1 = '1.23.4';
        $custom1 = '; '.$segment1.' ('.$version1.')';
        // Add extra spaces to ensure they're trimmed
        Config::addCustomUserAgent(' '.$segment1.' ', ' '.$version1.' ');

        $this->assertEquals(
            $this->default.$custom1,
            Config::getUserAgent()
        );

        $segment2 = 'Framework Xtra lib';
        $version2 = '1.0.4';
        $custom2 = '; '.$segment2.' ('.$version2.')';
        // Add extra spaces to ensure they're trimmed
        Config::addCustomUserAgent(' '.$segment2.' ', ' '.$version2.' ');

        $this->assertEquals(
            $this->default.$custom1.$custom2,
            Config::getUserAgent()
        );
    }
}
