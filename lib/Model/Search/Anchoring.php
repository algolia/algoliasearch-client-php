<?php

namespace Algolia\AlgoliaSearch\Model\Search;

use Algolia\AlgoliaSearch\ObjectSerializer;

/**
 * Anchoring Class Doc Comment
 *
 * @category Class
 * @description Whether the pattern parameter must match the beginning or the end of the query string, or both, or none.
 * @package Algolia\AlgoliaSearch
 */
class Anchoring
{
    /**
     * Possible values of this enum
     */
    public const IS = 'is';

    public const STARTS_WITH = 'startsWith';

    public const ENDS_WITH = 'endsWith';

    public const CONTAINS = 'contains';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [self::IS, self::STARTS_WITH, self::ENDS_WITH, self::CONTAINS];
    }
}
