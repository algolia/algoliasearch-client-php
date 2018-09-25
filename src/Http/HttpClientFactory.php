<?php

namespace Algolia\AlgoliaSearch\Http;

use Algolia\AlgoliaSearch\Interfaces\ClientConfigInterface;

final class HttpClientFactory
{
    private static $httpClientConstructor;

    public static function get(ClientConfigInterface $config)
    {
        if (!is_callable(self::$httpClientConstructor)) {
            if (class_exists('\GuzzleHttp\Client')) {
                self::set(function () use ($config) {
                    return new \Algolia\AlgoliaSearch\Http\Guzzle6HttpClient($config);
                });
            } else {
                self::set(function () use ($config) {
                    return new \Algolia\AlgoliaSearch\Http\Php53HttpClient($config);
                });
            }
        }

        return forward_static_call(self::$httpClientConstructor, $config);
    }

    public static function set($httpClientConstructor)
    {
        if (!is_callable($httpClientConstructor)) {
            throw new \InvalidArgumentException(
                'setHttpClient requires a function that build the HttpClient.'
            );
        }

        self::$httpClientConstructor = $httpClientConstructor;
    }

    public static function reset()
    {
        self::$httpClientConstructor = null;
    }
}
