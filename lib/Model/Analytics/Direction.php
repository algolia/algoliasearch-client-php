<?php

// This file is generated, manual changes will be lost - read more on https://github.com/algolia/api-clients-automation.

namespace Algolia\AlgoliaSearch\Model\Analytics;

/**
 * Direction Class Doc Comment
 *
 * @category Class
 * @package Algolia\AlgoliaSearch
 */
class Direction
{
    /**
     * Possible values of this enum
     */
    const ASC = 'asc';

    const DESC = 'desc';

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [self::ASC, self::DESC];
    }
}
