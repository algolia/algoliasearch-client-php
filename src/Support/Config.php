<?php

namespace Algolia\AlgoliaSearch\Support;

final class Config
{
    const VERSION = '2.0.0';

    public static $waitTaskRetry = 100;

    private static $userAgent;
    private static $customUserAgent = '';

    private static $readTimeout = 5;
    private static $writeTimeout = 5;
    private static $connectTimeout = 2;

    private static $httpClientConstructor;

    public static function getUserAgent()
    {
        if (!static::$userAgent) {
            static::$userAgent = 'PHP ('.str_replace(PHP_EXTRA_VERSION, '', PHP_VERSION).'); ';
            if (defined('HHVM_VERSION')) {
                static::$userAgent .= '; HHVM ('.HHVM_VERSION.')';
            }
            static::$userAgent .= 'Algolia for PHP ('.self::VERSION.')';
        }

        return static::$userAgent.static::$customUserAgent;
    }

    public static function addCustomUserAgent($segment, $version)
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

    public static function getHttpClient()
    {
        if (!is_callable(self::$httpClientConstructor)) {
            if (class_exists('\GuzzleHttp\Client')) {
                self::setHttpClient(function () {
                    return new \Algolia\AlgoliaSearch\Http\Guzzle6HttpClient(new \GuzzleHttp\Client());
                });
            } else {
                self::setHttpClient(function () {
                    return new \Algolia\AlgoliaSearch\Http\Php53HttpClient();
                });
            }
        }

        return forward_static_call(self::$httpClientConstructor);
    }

    public static function setHttpClient($httpClientConstructor)
    {
        if (!is_callable($httpClientConstructor)) {
            throw new \InvalidArgumentException(
                "setHttpClient requires a function that build the HttpClient."
            );
        }

        self::$httpClientConstructor = $httpClientConstructor;
    }
}
