<?php

namespace Algolia\AlgoliaSearch\Model\Search;

use Algolia\AlgoliaSearch\ObjectSerializer;

/**
 * Action Class Doc Comment
 *
 * @category Class
 * @description type of operation.
 * @package Algolia\AlgoliaSearch
 */
class Action
{
    /**
     * Possible values of this enum
     */
    public const ADD_OBJECT = 'addObject';

    public const UPDATE_OBJECT = 'updateObject';

    public const PARTIAL_UPDATE_OBJECT = 'partialUpdateObject';

    public const PARTIAL_UPDATE_OBJECT_NO_CREATE = 'partialUpdateObjectNoCreate';

    public const DELETE_OBJECT = 'deleteObject';

    public const DELETE = 'delete';

    public const CLEAR = 'clear';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [
            self::ADD_OBJECT,
            self::UPDATE_OBJECT,
            self::PARTIAL_UPDATE_OBJECT,
            self::PARTIAL_UPDATE_OBJECT_NO_CREATE,
            self::DELETE_OBJECT,
            self::DELETE,
            self::CLEAR,
        ];
    }
}
