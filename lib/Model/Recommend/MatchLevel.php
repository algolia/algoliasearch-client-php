<?php

namespace Algolia\AlgoliaSearch\Model\Recommend;

use Algolia\AlgoliaSearch\ObjectSerializer;

/**
 * MatchLevel Class Doc Comment
 *
 * @category Class
 * @description Indicates how well the attribute matched the search query.
 * @package Algolia\AlgoliaSearch
 */
class MatchLevel
{
    /**
     * Possible values of this enum
     */
    public const NONE = 'none';

    public const PARTIAL = 'partial';

    public const FULL = 'full';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [self::NONE, self::PARTIAL, self::FULL];
    }
}
