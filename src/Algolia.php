<?php

namespace Algolia\AlgoliaSearch;

use Algolia\AlgoliaSearch\Cache\NullCacheDriver;
use Algolia\AlgoliaSearch\Log\Logger;
use Psr\Log\LoggerInterface;
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

    /**
     * Holds an instance of the logger (PSR-3).
     *
     * @var \Psr\Log\LoggerInterface|null
     */
    private static $logger;

    public static function isCacheEnabled()
    {
        if (null === self::$cache) {
            return false;
        }

        return !self::getCache() instanceof NullCacheDriver;
    }

    /**
     * Gets the cache instance.
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
     * Sets the cache instance.
     *
     * @param \Psr\SimpleCache\CacheInterface $cache
     */
    public static function setCache(CacheInterface $cache)
    {
        self::$cache = $cache;
    }

    /**
     * Gets the logger instance
     *
     * @return \Psr\Log\LoggerInterface
     */
    public static function getLogger()
    {
        if (!self::$logger) {
            self::setLogger(new Logger());
        }

        return self::$logger;
    }

    /**
     * Sets the logger instance
     *
     * @param \Psr\Log\LoggerInterface $logger
     */
    public static function setLogger(LoggerInterface $logger)
    {
        self::$logger = $logger;
    }
}
