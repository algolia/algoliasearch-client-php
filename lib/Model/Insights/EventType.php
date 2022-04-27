<?php

namespace Algolia\AlgoliaSearch\Model\Insights;

use Algolia\AlgoliaSearch\ObjectSerializer;

/**
 * EventType Class Doc Comment
 *
 * @category Class
 * @description An eventType can be a click, a conversion, or a view.
 * @package Algolia\AlgoliaSearch
 */
class EventType
{
    /**
     * Possible values of this enum
     */
    public const CLICK = 'click';

    public const CONVERSION = 'conversion';

    public const VIEW = 'view';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [self::CLICK, self::CONVERSION, self::VIEW];
    }
}
