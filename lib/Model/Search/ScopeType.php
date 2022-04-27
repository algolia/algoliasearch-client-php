<?php

namespace Algolia\AlgoliaSearch\Model\Search;

use Algolia\AlgoliaSearch\ObjectSerializer;

/**
 * ScopeType Class Doc Comment
 *
 * @category Class
 * @package Algolia\AlgoliaSearch
 */
class ScopeType
{
    /**
     * Possible values of this enum
     */
    public const SETTINGS = 'settings';

    public const SYNONYMS = 'synonyms';

    public const RULES = 'rules';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [self::SETTINGS, self::SYNONYMS, self::RULES];
    }
}
