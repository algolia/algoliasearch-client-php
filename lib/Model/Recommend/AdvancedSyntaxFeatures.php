<?php

namespace Algolia\AlgoliaSearch\Model\Recommend;

use Algolia\AlgoliaSearch\ObjectSerializer;

/**
 * AdvancedSyntaxFeatures Class Doc Comment
 *
 * @category Class
 * @package Algolia\AlgoliaSearch
 */
class AdvancedSyntaxFeatures
{
    /**
     * Possible values of this enum
     */
    public const EXACT_PHRASE = 'exactPhrase';

    public const EXCLUDE_WORDS = 'excludeWords';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [self::EXACT_PHRASE, self::EXCLUDE_WORDS];
    }
}
