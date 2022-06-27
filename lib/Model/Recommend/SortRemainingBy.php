<?php

namespace Algolia\AlgoliaSearch\Model\Recommend;

/**
 * SortRemainingBy Class Doc Comment
 *
 * @category Class
 * @description How to display the remaining items.   - &#x60;count&#x60;: facet count (descending).   - &#x60;alpha&#x60;: alphabetical (ascending).   - &#x60;hidden&#x60;: show only pinned values.
 *
 * @package Algolia\AlgoliaSearch
 */
class SortRemainingBy
{
    /**
     * Possible values of this enum
     */
    const COUNT = 'count';

    const ALPHA = 'alpha';

    const HIDDEN = 'hidden';

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [self::COUNT, self::ALPHA, self::HIDDEN];
    }
}
