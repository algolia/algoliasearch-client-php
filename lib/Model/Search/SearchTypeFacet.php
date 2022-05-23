<?php

namespace Algolia\AlgoliaSearch\Model\Search;

/**
 * SearchTypeFacet Class Doc Comment
 *
 * @category Class
 * @description Perform a search query with &#x60;default&#x60;, will search for facet values if &#x60;facet&#x60; is given.
 *
 * @package Algolia\AlgoliaSearch
 */
class SearchTypeFacet
{
    /**
     * Possible values of this enum
     */
    const FACET = 'facet';

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [self::FACET];
    }
}
