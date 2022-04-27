<?php

namespace Algolia\AlgoliaSearch\Model\Recommend;

use Algolia\AlgoliaSearch\ObjectSerializer;

/**
 * TrendingModels Class Doc Comment
 *
 * @category Class
 * @description The trending model to use.
 * @package Algolia\AlgoliaSearch
 */
class TrendingModels
{
    /**
     * Possible values of this enum
     */
    public const FACETS = 'trending-facets';

    public const ITEMS = 'trending-items';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [self::FACETS, self::ITEMS];
    }
}
