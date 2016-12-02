<?php

namespace AlgoliaSearch;

class WriteHostsHandler extends HostsHandler
{
    private static $position = null;

    /**
     * @return array
     */
    protected function getDefaultHosts($applicationID, $placesEnabled = false)
    {
        return array(
            $applicationID.'.algolia.net',
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
