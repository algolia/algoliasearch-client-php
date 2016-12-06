<?php

namespace AlgoliaSearch;

class ReadHostsHandler extends HostsHandler
{
    private static $position = null;

    protected function getPosition()
    {
        return self::$position;
    }

    protected function setPosition($position)
    {
        self::$position = $position;
    }

    public static function resetPosition()
    {
        self::$position = null;
    }
}
