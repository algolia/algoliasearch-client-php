<?php

namespace Algolia\AlgoliaSearch\Model\Search;

use Algolia\AlgoliaSearch\ObjectSerializer;

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
    public const ALL = 'all';

    public const QUERY = 'query';

    public const BUILD = 'build';

    public const ERROR = 'error';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [self::ALL, self::QUERY, self::BUILD, self::ERROR];
    }
}
