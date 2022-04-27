<?php

namespace Algolia\AlgoliaSearch\Model\Search;

use Algolia\AlgoliaSearch\ObjectSerializer;

/**
 * DictionaryType Class Doc Comment
 *
 * @category Class
 * @package Algolia\AlgoliaSearch
 */
class DictionaryType
{
    /**
     * Possible values of this enum
     */
    public const PLURALS = 'plurals';

    public const STOPWORDS = 'stopwords';

    public const COMPOUNDS = 'compounds';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [self::PLURALS, self::STOPWORDS, self::COMPOUNDS];
    }
}
