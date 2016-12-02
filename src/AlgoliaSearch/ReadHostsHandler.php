<?php

namespace AlgoliaSearch;

class ReadHostsHandler extends HostsHandler
{
    private static $position = null;

    /**
     * @param bool $placesEnabled
     *
     * @return array
     */
    protected function getDefaultHosts($applicationID, $placesEnabled = false)
    {
        if ($placesEnabled) {
            return array(
                'places-dsn.algolia.net',
                'places-1.algolianet.com',
                'places-2.algolianet.com',
                'places-3.algolianet.com',
            );
        }

        return array(
            $applicationID.'-dsn.algolia.net',
            $applicationID.'-1.algolianet.com',
            $applicationID.'-2.algolianet.com',
            $applicationID.'-3.algolianet.com',
        );
    }

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
