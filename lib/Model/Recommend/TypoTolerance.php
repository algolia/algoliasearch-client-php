<?php

namespace Algolia\AlgoliaSearch\Model\Recommend;

use Algolia\AlgoliaSearch\ObjectSerializer;

/**
 * TypoTolerance Class Doc Comment
 *
 * @category Class
 * @description Controls whether typo tolerance is enabled and how it is applied.
 * @package Algolia\AlgoliaSearch
 */
class TypoTolerance
{
    /**
     * Possible values of this enum
     */
    public const TRUE = 'true';

    public const FALSE = 'false';

    public const MIN = 'min';

    public const STRICT = 'strict';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [self::TRUE, self::FALSE, self::MIN, self::STRICT];
    }
}
