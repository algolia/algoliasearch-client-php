<?php

// This file is generated, manual changes will be lost - read more on https://github.com/algolia/api-clients-automation.

namespace Algolia\AlgoliaSearch\Model\Search;

/**
 * TypoToleranceEnum Class Doc Comment
 *
 * @category Class
 * @package Algolia\AlgoliaSearch
 */
class TypoToleranceEnum
{
    /**
     * Possible values of this enum
     */
    const MIN = 'min';

    const STRICT = 'strict';

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [self::MIN, self::STRICT];
    }
}
