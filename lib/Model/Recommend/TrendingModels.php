<?php

// This file is generated, manual changes will be lost - read more on https://github.com/algolia/api-clients-automation.

namespace Algolia\AlgoliaSearch\Model\Recommend;

/**
 * TrendingModels Class Doc Comment
 *
 * @category Class
 *
 * @description The trending model to use.
 *
 * @package Algolia\AlgoliaSearch
 */
class TrendingModels
{
    /**
     * Possible values of this enum
     */
    const FACETS = 'trending-facets';

    const ITEMS = 'trending-items';

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [self::FACETS, self::ITEMS];
    }
}
