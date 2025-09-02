<?php

namespace Algolia\AlgoliaSearch\Support;

use Algolia\AlgoliaSearch\Algolia;
use GuzzleHttp\ClientInterface;

final class AlgoliaAgent
{
    private static $value;

    private static $customSegments = [];

    public static function get($clientName)
    {
        if (!isset(self::$value[$clientName])) {
            self::$value[$clientName] = self::getComputedValue($clientName);
        }

        return self::$value[$clientName];
    }

    public static function addAlgoliaAgent($clientName, $segment, $version)
    {
        self::$value[$clientName] = null;
        self::$customSegments[trim($segment, ' ')] = trim($version, ' ');
    }

    private static function getComputedValue($clientName)
    {
        $ua = [];
        $segments = array_merge(
            self::getDefaultSegments($clientName),
            self::$customSegments
        );

        foreach ($segments as $segment => $version) {
            $ua[] = $segment.' ('.$version.')';
        }

        return implode('; ', $ua);
    }

    private static function getDefaultSegments($clientName)
    {
        $segments = [];

        $segments['Algolia for PHP'] = Algolia::VERSION;
        $segments[$clientName] = Algolia::VERSION;
        $segments['PHP'] = rtrim(
            str_replace(PHP_EXTRA_VERSION, '', PHP_VERSION),
            '-'
        );
        if (defined('HHVM_VERSION')) {
            $segments['HHVM'] = HHVM_VERSION;
        }
        if (interface_exists('\GuzzleHttp\ClientInterface')) {
            if (defined('\GuzzleHttp\ClientInterface::VERSION')) {
                $segments['Guzzle'] = ClientInterface::VERSION;
            } else {
                $segments['Guzzle']
                    = ClientInterface::MAJOR_VERSION;
            }
        }

        return $segments;
    }
}
