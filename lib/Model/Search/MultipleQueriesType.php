<?php

namespace Algolia\AlgoliaSearch\Model\Search;

use Algolia\AlgoliaSearch\ObjectSerializer;

/**
 * MultipleQueriesType Class Doc Comment
 *
 * @category Class
 * @description Perform a search query with &#x60;default&#x60;, will search for facet values if &#x60;facet&#x60; is given.
 * @package Algolia\AlgoliaSearch
 */
class MultipleQueriesType
{
    /**
     * Possible values of this enum
     */
    public const _DEFAULT = 'default';

    public const FACET = 'facet';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [self::_DEFAULT, self::FACET];
    }
}
