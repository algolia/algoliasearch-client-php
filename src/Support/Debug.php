<?php

namespace Algolia\AlgoliaSearch\Support;

class Debug
{
    private static $debug = false;

    private static $handler;

    public static function isEnabled()
    {
        return self::$debug;
    }

    public static function disable()
    {
        self::$debug = false;
    }

    public static function enable()
    {
        if (!is_callable(self::$handler)) {
            self::setHandler(function () {
                $args = func_get_args();
                foreach ($args as $arg) {
                    if (function_exists('dump')) {
                        dump($arg);
                    } else {
                        var_dump($arg);
                    }
                }
            });
        }

        self::$debug = true;
    }

    public static function handle()
    {
        if (!is_callable(self::$handler)) {
            return;
        }

        forward_static_call_array(self::$handler, func_get_args());
    }

    public static function setHandler(callable $handler)
    {
        self::$handler = $handler;
    }
}
