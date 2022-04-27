<?php

namespace Algolia\AlgoliaSearch\Model\Recommend;

use Algolia\AlgoliaSearch\ObjectSerializer;

/**
 * AlternativesAsExact Class Doc Comment
 *
 * @category Class
 * @package Algolia\AlgoliaSearch
 */
class AlternativesAsExact
{
    /**
     * Possible values of this enum
     */
    public const IGNORE_PLURALS = 'ignorePlurals';

    public const SINGLE_WORD_SYNONYM = 'singleWordSynonym';

    public const MULTI_WORDS_SYNONYM = 'multiWordsSynonym';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [
            self::IGNORE_PLURALS,
            self::SINGLE_WORD_SYNONYM,
            self::MULTI_WORDS_SYNONYM,
        ];
    }
}
