<?php

namespace Algolia\AlgoliaSearch;

final class Config
{
    const VERSION = '2.0.0';

    public static $waitTaskRetry = 100;

    private static $userAgent;
    private static $customUserAgent = '';

    static public function getUserAgent()
    {
        if (! static::$userAgent) {
            static::$userAgent =
                'PHP ('.str_replace(PHP_EXTRA_VERSION, '', PHP_VERSION).'); ' .
                'Algolia for PHP ('.self::VERSION.')';
        }

        return static::$userAgent.static::$customUserAgent;
    }

    static public function addCustomUserAgent($segment, $version)
    {
        static::$customUserAgent .= '; '.trim($segment, ' ').' ('.trim($version, ' ').')';
    }
}
