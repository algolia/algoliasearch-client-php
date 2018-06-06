<?php

namespace AlgoliaSearch\Tests;

use AlgoliaSearch\Version;

class VersionTest extends AlgoliaSearchTestCase
{
    private $php;

    public function setUp()
    {
        $version = PHP_VERSION;
        if ($hyphen = strpos($version, '-')) {
            $version = substr($version, 0, $hyphen);
        }
        $this->php = 'PHP ('.$version.')';

        if (defined('HHVM_VERSION')) {
            $this->php .= '; HHVM ('.HHVM_VERSION.')';
        }
    }

    public function tearDown()
    {
        Version::$custom_value = '';
        Version::clearUserAgentSuffixesAndPrefixes();
    }

    public function testVersionAddOnePrefixAndOneSuffix()
    {
        $userAgent = Version::getUserAgent();
        $this->assertRegExp('/^Algolia for PHP \(\d+\.\d+\.\d+\); PHP \(\d+\.\d+\.\d+\)(; HHVM \(\d+\.\d+\.\d+\))?$/', $userAgent);

        Version::addPrefixUserAgentSegment('Prefix integration', '0.0.8');
        Version::addSuffixUserAgentSegment('Suffix platform', '1.2.3');

        $userAgent = Version::getUserAgent();
        $this->assertEquals('Algolia for PHP ('.Version::VALUE.'); '.$this->php.'; Prefix integration (0.0.8); Suffix platform (1.2.3)', $userAgent);
    }

    public function testVersionAddTwoPrefixAndTwoSuffix()
    {
        Version::addPrefixUserAgentSegment('Prefix integration', '0.0.8');
        Version::addSuffixUserAgentSegment('Suffix platform', '1.2.3');
        Version::addPrefixUserAgentSegment('Another prefix', '5.6.7');
        Version::addSuffixUserAgentSegment('Different suffix', '7.8.9');

        $userAgent = Version::getUserAgent();
        $this->assertEquals('Algolia for PHP ('.Version::VALUE.'); '.$this->php.'; Prefix integration (0.0.8); Suffix platform (1.2.3); Another prefix (5.6.7); Different suffix (7.8.9)', $userAgent);

        // Should be "X.Y.Z"
        $version = Version::get();
        $this->assertRegExp('/^\d+\.\d+\.\d+$/', $version);
    }

    public function testVersionAddTwoPrefixAndTwoSuffixAndCustomValue()
    {
        Version::addPrefixUserAgentSegment('Prefix integration', '0.0.8');
        Version::addSuffixUserAgentSegment('Suffix platform', '1.2.3');
        Version::addPrefixUserAgentSegment('Another prefix', '5.6.7');
        Version::addSuffixUserAgentSegment('Different suffix', '7.8.9');
        Version::$custom_value = ' custom_value';

        $this->assertStringEndsWith(' custom_value', Version::getUserAgent());

        // Shoul be "X.Y.Z custom_value"
        $this->assertRegExp('/^\d+\.\d+\.\d+ custom_value$/', Version::get());
    }

    public function testVersionDuplicatesPrefix()
    {
        Version::addPrefixUserAgentSegment('Another prefix', '5.6.7');
        Version::addPrefixUserAgentSegment('Another prefix', '5.6.7');

        $userAgent = Version::getUserAgent();
        $this->assertEquals('Algolia for PHP ('.Version::VALUE.'); '.$this->php.'; Another prefix (5.6.7)', $userAgent);
    }

    public function testVersionDuplicatesSuffix()
    {
        Version::addSuffixUserAgentSegment('Another suffix', '5.6.7');
        Version::addSuffixUserAgentSegment('Another suffix', '5.6.7');

        $userAgent = Version::getUserAgent();
        $this->assertEquals('Algolia for PHP ('.Version::VALUE.'); '.$this->php.'; Another suffix (5.6.7)', $userAgent);
    }

    public function testVersionTwoPrefix()
    {
        Version::addPrefixUserAgentSegment('prefix', '5.6.7');
        Version::addPrefixUserAgentSegment('Another prefix', '5.6.7');

        $userAgent = Version::getUserAgent();
        $this->assertEquals('Algolia for PHP ('.Version::VALUE.'); '.$this->php.'; prefix (5.6.7); Another prefix (5.6.7)', $userAgent);
    }
}
