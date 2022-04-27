<?php

namespace Algolia\AlgoliaSearch\Model\Analytics;

use Algolia\AlgoliaSearch\ObjectSerializer;

/**
 * OrderBy Class Doc Comment
 *
 * @category Class
 * @package Algolia\AlgoliaSearch
 */
class OrderBy
{
    /**
     * Possible values of this enum
     */
    public const SEARCH_COUNT = 'searchCount';

    public const CLICK_THROUGH_RATE = 'clickThroughRate';

    public const CONVERSION_RATE = 'conversionRate';

    public const AVERAGE_CLICK_POSITION = 'averageClickPosition';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [
            self::SEARCH_COUNT,
            self::CLICK_THROUGH_RATE,
            self::CONVERSION_RATE,
            self::AVERAGE_CLICK_POSITION,
        ];
    }
}
