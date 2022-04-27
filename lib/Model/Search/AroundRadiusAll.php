<?php

namespace Algolia\AlgoliaSearch\Model\Search;

use Algolia\AlgoliaSearch\ObjectSerializer;

/**
 * AroundRadiusAll Class Doc Comment
 *
 * @category Class
 * @package Algolia\AlgoliaSearch
 */
class AroundRadiusAll
{
    /**
     * Possible values of this enum
     */
    public const ALL = 'all';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [self::ALL];
    }
}
