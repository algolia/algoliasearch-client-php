<?php

// This file is generated, manual changes will be lost - read more on https://github.com/algolia/api-clients-automation.

namespace Algolia\AlgoliaSearch\Model\Search;

/**
 * DictionaryAction Class Doc Comment
 *
 * @category Class
 *
 * @description Actions to perform.
 *
 * @package Algolia\AlgoliaSearch
 */
class DictionaryAction
{
    /**
     * Possible values of this enum
     */
    const ADD_ENTRY = 'addEntry';

    const DELETE_ENTRY = 'deleteEntry';

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [self::ADD_ENTRY, self::DELETE_ENTRY];
    }
}
