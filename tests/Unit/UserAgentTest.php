<?php

namespace Algolia\AlgoliaSearch\Tests\Unit;

use Algolia\AlgoliaSearch\Algolia;
use Algolia\AlgoliaSearch\Support\UserAgent;
use PHPUnit\Framework\TestCase;

class UserAgentTest extends TestCase
{
    private $default;

    public function setUp()
    {
        $this->default = 'Algolia for PHP ('.Algolia::VERSION.'); ';
        $this->default .= 'PHP ('.rtrim(str_replace(PHP_EXTRA_VERSION, '', PHP_VERSION), '-').')';
        if (defined('HHVM_VERSION')) {
            $this->default .= '; HHVM ('.HHVM_VERSION.')';
        }
        if (interface_exists('\GuzzleHttp\ClientInterface')) {
            if (defined('\GuzzleHttp\ClientInterface::VERSION')) {
                $this->default .= '; Guzzle ('.\GuzzleHttp\ClientInterface::VERSION.')';
            } else {
                $this->default .= '; Guzzle ('.\GuzzleHttp\ClientInterface::MAJOR_VERSION.')';
            }
        }
    }

    public function testDefaultUserAgent()
    {
        $this->assertRegExp('/^Algolia for PHP \(\d+\.\d+\.\d+\); PHP \(\d+\.\d+\.\d+\).*$/', UserAgent::get());

        $this->assertEquals($this->default, UserAgent::get());
    }

    public function testWithCustomUserAgent()
    {
        $segment1 = 'Framework blah integration';
        $version1 = '1.23.4';
        $custom1 = '; '.$segment1.' ('.$version1.')';
        // Add extra spaces to ensure they're trimmed
        UserAgent::addCustomUserAgent(' '.$segment1.' ', ' '.$version1.' ');

        $this->assertEquals(
            $this->default.$custom1,
            UserAgent::get()
        );

        $segment2 = 'Framework Xtra lib';
        $version2 = '1.0.4';
        $custom2 = '; '.$segment2.' ('.$version2.')';
        // Add extra spaces to ensure they're trimmed
        UserAgent::addCustomUserAgent(' '.$segment2.' ', ' '.$version2.' ');

        $this->assertEquals(
            $this->default.$custom1.$custom2,
            UserAgent::get()
        );
    }
}
