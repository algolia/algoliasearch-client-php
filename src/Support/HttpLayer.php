<?php

namespace Algolia\AlgoliaSearch\Support;

final class HttpLayer
{
    private static $httpClientConstructor;

    public static function get()
    {
        if (!is_callable(self::$httpClientConstructor)) {
            if (class_exists('\GuzzleHttp\Client')) {
                self::set(function () {
                    return new \Algolia\AlgoliaSearch\Http\Guzzle6HttpClient(new \GuzzleHttp\Client());
                });
            } else {
                self::set(function () {
                    return new \Algolia\AlgoliaSearch\Http\Php53HttpClient();
                });
            }
        }

        return forward_static_call(self::$httpClientConstructor);
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
}
