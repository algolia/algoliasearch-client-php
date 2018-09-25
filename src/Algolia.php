<?php

namespace Algolia\AlgoliaSearch;

use Algolia\AlgoliaSearch\Cache\NullCacheDriver;
use Psr\SimpleCache\CacheInterface;

class Algolia
{
    const VERSION = '2.0.0';

    /**
     * Holds an instance of the simple cache repository (PSR-16).
     *
     * @var \Psr\SimpleCache\CacheInterface|null
     */
    private static $cache;

    public static function isCacheEnabled()
    {
        if (null === self::$cache) {
            return false;
        }

        return !self::getCache() instanceof NullCacheDriver;
    }

    /**
     * Gets the cache instance of the object.
     *
     * @return \Psr\SimpleCache\CacheInterface
     */
    public static function getCache()
    {
        if (!self::$cache) {
            self::setCache(new NullCacheDriver());
        }

        return self::$cache;
    }

    /**
     * Sets the cache instance of the object.
     *
     * @param \Psr\SimpleCache\CacheInterface $cache
     */
    public static function setCache(CacheInterface $cache)
    {
        self::$cache = $cache;
    }
}
