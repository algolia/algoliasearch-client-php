<?php

namespace Algolia\AlgoliaSearch\Log;

use Psr\Log\LoggerInterface;

final class LogManager
{
    /**
     * Holds the current logger.
     *
     * @var \Psr\Log\LoggerInterface;
     */
    private static $logger;

    /**
     * Sets a logger instance on the object.
     *
     * @param \Psr\Log\LoggerInterface $logger
     *
     * @return void
     */
    public static function setLogger(LoggerInterface $logger)
    {
        self::$logger = $logger;
    }

    /**
     * Gets a logger instance on the object.
     *
     * @return \Psr\Log\LoggerInterface $logger
     */
    public static function getLogger()
    {
        if (null === self::$logger) {
            self::$logger = new Logger();
        }

        return self::$logger;
    }
}