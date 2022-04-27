<?php

namespace Algolia\AlgoliaSearch\Model\Search;

use Algolia\AlgoliaSearch\ObjectSerializer;

/**
 * MultipleQueriesStrategy Class Doc Comment
 *
 * @category Class
 * @package Algolia\AlgoliaSearch
 */
class MultipleQueriesStrategy
{
    /**
     * Possible values of this enum
     */
    public const NONE = 'none';

    public const STOP_IF_ENOUGH_MATCHES = 'stopIfEnoughMatches';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [self::NONE, self::STOP_IF_ENOUGH_MATCHES];
    }
}
