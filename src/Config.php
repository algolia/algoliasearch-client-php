<?php

namespace Algolia\AlgoliaSearch;

final class Config
{
    const VERSION = '2.0.0';

    public static $waitTaskRetry = 100;

    private static $userAgent;
    private static $customUserAgent = '';


    private static $readTimeout = 5;
    private static $writeTimeout = 5;
    private static $connectTimeout = 2;

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

    public static function getReadTimeout()
    {
        return self::$readTimeout;
    }

    public static function setReadTimeout($readTimeout)
    {
        self::$readTimeout = $readTimeout;
    }

    public static function getWriteTimeout()
    {
        return self::$writeTimeout;
    }

    public static function setWriteTimeout($writeTimeout)
    {
        self::$writeTimeout = $writeTimeout;
    }

    public static function getConnectTimeout()
    {
        return self::$connectTimeout;
    }

    public static function setConnectTimeout($connectTimeout)
    {
        self::$connectTimeout = $connectTimeout;
    }


}
