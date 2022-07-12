<?php

// This file is generated, manual changes will be lost - read more on https://github.com/algolia/api-clients-automation.

namespace Algolia\AlgoliaSearch\Model\Search;

/**
 * LogType Class Doc Comment
 *
 * @category Class
 * @package Algolia\AlgoliaSearch
 */
class LogType
{
    /**
     * Possible values of this enum
     */
    const ALL = 'all';

    const QUERY = 'query';

    const BUILD = 'build';

    const ERROR = 'error';

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [self::ALL, self::QUERY, self::BUILD, self::ERROR];
    }
}
