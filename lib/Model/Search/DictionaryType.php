<?php

// This file is generated, manual changes will be lost - read more on https://github.com/algolia/api-clients-automation.

namespace Algolia\AlgoliaSearch\Model\Search;

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
    const PLURALS = 'plurals';

    const STOPWORDS = 'stopwords';

    const COMPOUNDS = 'compounds';

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [self::PLURALS, self::STOPWORDS, self::COMPOUNDS];
    }
}
