<?php

namespace Algolia\AlgoliaSearch\Model\Search;

use Algolia\AlgoliaSearch\ObjectSerializer;

/**
 * DictionaryAction Class Doc Comment
 *
 * @category Class
 * @description Actions to perform.
 * @package Algolia\AlgoliaSearch
 */
class DictionaryAction
{
    /**
     * Possible values of this enum
     */
    public const ADD_ENTRY = 'addEntry';

    public const DELETE_ENTRY = 'deleteEntry';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [self::ADD_ENTRY, self::DELETE_ENTRY];
    }
}
