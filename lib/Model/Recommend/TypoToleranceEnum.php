<?php

namespace Algolia\AlgoliaSearch\Model\Recommend;

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
