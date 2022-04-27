<?php

namespace Algolia\AlgoliaSearch\Model\Recommend;

use Algolia\AlgoliaSearch\ObjectSerializer;

/**
 * RemoveWordsIfNoResults Class Doc Comment
 *
 * @category Class
 * @description Selects a strategy to remove words from the query when it doesn&#39;t match any hits.
 * @package Algolia\AlgoliaSearch
 */
class RemoveWordsIfNoResults
{
    /**
     * Possible values of this enum
     */
    public const NONE = 'none';

    public const LAST_WORDS = 'lastWords';

    public const FIRST_WORDS = 'firstWords';

    public const ALL_OPTIONAL = 'allOptional';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [
            self::NONE,
            self::LAST_WORDS,
            self::FIRST_WORDS,
            self::ALL_OPTIONAL,
        ];
    }
}
