<?php

namespace Algolia\AlgoliaSearch\Log;

use Psr\Log\AbstractLogger;

final class Logger extends AbstractLogger
{
    /**
     * Whether the logger is enabled or not.
     *
     * @var bool
     */
    private static $isEnabled = false;

    /**
     * Disables the logger.
     */
    public static function disable()
    {
        self::$isEnabled = false;
    }

    /**
     * Enables the logger.
     */
    public static function enable()
    {
        self::$isEnabled = true;
    }

    /**
     * {@inheritdoc}
     */
    public function log($level, $message, array $context = array())
    {
        if (self::$isEnabled) {
            $logMessage = array(
                'level' => $level,
                'message' => $message,
                'context' => $context,
            );

            if (function_exists('dump')) {
                dump($logMessage);
            } else {
                var_dump($logMessage);
            }
        }
    }
}
