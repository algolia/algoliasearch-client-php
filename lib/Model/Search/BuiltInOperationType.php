<?php

namespace Algolia\AlgoliaSearch\Model\Search;

use Algolia\AlgoliaSearch\ObjectSerializer;

/**
 * BuiltInOperationType Class Doc Comment
 *
 * @category Class
 * @description The operation to apply on the attribute.
 * @package Algolia\AlgoliaSearch
 */
class BuiltInOperationType
{
    /**
     * Possible values of this enum
     */
    public const INCREMENT = 'Increment';

    public const DECREMENT = 'Decrement';

    public const ADD = 'Add';

    public const REMOVE = 'Remove';

    public const ADD_UNIQUE = 'AddUnique';

    public const INCREMENT_FROM = 'IncrementFrom';

    public const INCREMENT_SET = 'IncrementSet';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [
            self::INCREMENT,
            self::DECREMENT,
            self::ADD,
            self::REMOVE,
            self::ADD_UNIQUE,
            self::INCREMENT_FROM,
            self::INCREMENT_SET,
        ];
    }
}
