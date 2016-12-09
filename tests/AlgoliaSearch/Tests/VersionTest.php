<?php

namespace AlgoliaSearch\Tests;

use AlgoliaSearch\Version;

class VersionTest extends AlgoliaSearchTestCase
{
    public function testVersion()
    {
        $userAgent = Version::getUserAgent();
        $this->assertRegExp('/^Algolia for PHP \(\d+\.\d+\.\d+\)$/', $userAgent);

        Version::addPrefixUserAgentSegment('Prefix integration', '0.0.8');
        Version::addSuffixUserAgentSegment('Suffix platform', '1.2.3');

        // Should be "Prefix 0.0.8; Algolia for PHP (X.Y.Z); Suffix platform (1.2.3)"
        $userAgent = Version::getUserAgent();
        $this->assertRegExp('/^Prefix integration \(0\.0\.8\); Algolia for PHP \(\d+\.\d+\.\d+\); Suffix platform \(1\.2\.3\)$/', $userAgent);

        Version::addPrefixUserAgentSegment('Another prefix', '5.6.7');
        Version::addSuffixUserAgentSegment('Different suffix', '7.8.9');

        // Should be "Another prefix (5.6.7); Prefix 0.0.8; Algolia for PHP (X.Y.Z); Suffix platform (1.2.3); Different suffix (7.8.9)"
        $userAgent = Version::getUserAgent();
        $this->assertRegExp('/^Another prefix \(5\.6\.7\); Prefix integration \(0\.0\.8\); Algolia for PHP \(\d+\.\d+\.\d+\); Suffix platform \(1\.2\.3\); Different suffix \(7\.8\.9\)$/', $userAgent);

        // Should be "X.Y.Z"
        $version = Version::get();
        $this->assertRegExp('/^\d+\.\d+\.\d+$/', $version);

        Version::$custom_value = ' custom_value';

        $this->assertStringEndsWith(' custom_value', Version::getUserAgent());

        // Shoul be "X.Y.Z custom_value"
        $this->assertRegExp('/^\d+\.\d+\.\d+ custom_value$/', Version::get());
    }
}
