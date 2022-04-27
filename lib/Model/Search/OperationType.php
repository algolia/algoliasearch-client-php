<?php

namespace Algolia\AlgoliaSearch\Model\Search;

use Algolia\AlgoliaSearch\ObjectSerializer;

/**
 * OperationType Class Doc Comment
 *
 * @category Class
 * @description Type of operation to perform (move or copy).
 * @package Algolia\AlgoliaSearch
 */
class OperationType
{
    /**
     * Possible values of this enum
     */
    public const MOVE = 'move';

    public const COPY = 'copy';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [self::MOVE, self::COPY];
    }
}
