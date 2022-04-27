<?php

namespace Algolia\AlgoliaSearch\Model\Search;

use Algolia\AlgoliaSearch\ObjectSerializer;

/**
 * Acl Class Doc Comment
 *
 * @category Class
 * @package Algolia\AlgoliaSearch
 */
class Acl
{
    /**
     * Possible values of this enum
     */
    public const ADD_OBJECT = 'addObject';

    public const ANALYTICS = 'analytics';

    public const BROWSE = 'browse';

    public const DELETE_OBJECT = 'deleteObject';

    public const DELETE_INDEX = 'deleteIndex';

    public const EDIT_SETTINGS = 'editSettings';

    public const LIST_INDEXES = 'listIndexes';

    public const LOGS = 'logs';

    public const PERSONALIZATION = 'personalization';

    public const RECOMMENDATION = 'recommendation';

    public const SEARCH = 'search';

    public const SEE_UNRETRIEVABLE_ATTRIBUTES = 'seeUnretrievableAttributes';

    public const SETTINGS = 'settings';

    public const USAGE = 'usage';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [
            self::ADD_OBJECT,
            self::ANALYTICS,
            self::BROWSE,
            self::DELETE_OBJECT,
            self::DELETE_INDEX,
            self::EDIT_SETTINGS,
            self::LIST_INDEXES,
            self::LOGS,
            self::PERSONALIZATION,
            self::RECOMMENDATION,
            self::SEARCH,
            self::SEE_UNRETRIEVABLE_ATTRIBUTES,
            self::SETTINGS,
            self::USAGE,
        ];
    }
}
