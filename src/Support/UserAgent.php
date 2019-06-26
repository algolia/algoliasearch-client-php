<?php

namespace Algolia\AlgoliaSearch\Support;

use Algolia\AlgoliaSearch\Algolia;

/**
 * Class UserAgent.
 */
final class UserAgent
{
    /**
     * @var string
     */
    private static $value;

    /**
     * @var array
     */
    private static $customSegments = array();

    /**
     * @return string
     */
    public static function get()
    {
        if (null === self::$value) {
            self::$value = self::getComputedValue();
        }

        return self::$value;
    }

    /**
     * @param string $segment
     * @param string $version
     *
     * @return void
     */
    public static function addCustomUserAgent($segment, $version)
    {
        self::$value = null;
        self::$customSegments[trim($segment, ' ')] = trim($version, ' ');
    }

    /**
     * @return string
     */
    private static function getComputedValue()
    {
        $ua = array();
        $segments = array_merge(self::getDefaultSegments(), self::$customSegments);

        foreach ($segments as $segment => $version) {
            $ua[] = $segment.' ('.$version.')';
        }

        return implode('; ', $ua);
    }

    /**
     * @return array
     */
    private static function getDefaultSegments()
    {
        $segments = array();

        $segments['Algolia for PHP'] = Algolia::VERSION;
        $segments['PHP'] = rtrim(str_replace(PHP_EXTRA_VERSION, '', PHP_VERSION), '-');
        if (defined('HHVM_VERSION')) {
            $segments['HHVM'] = HHVM_VERSION;
        }
        if (interface_exists('\GuzzleHttp\ClientInterface')) {
            $segments['Guzzle'] = \GuzzleHttp\ClientInterface::VERSION;
        }

        return $segments;
    }
}
