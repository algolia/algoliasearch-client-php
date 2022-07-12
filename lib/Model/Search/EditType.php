<?php

// This file is generated, manual changes will be lost - read more on https://github.com/algolia/api-clients-automation.

namespace Algolia\AlgoliaSearch\Model\Search;

/**
 * EditType Class Doc Comment
 *
 * @category Class
 * @description Type of edit.
 *
 * @package Algolia\AlgoliaSearch
 */
class EditType
{
    /**
     * Possible values of this enum
     */
    const REMOVE = 'remove';

    const REPLACE = 'replace';

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [self::REMOVE, self::REPLACE];
    }
}
