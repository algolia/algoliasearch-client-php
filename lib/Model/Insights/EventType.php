<?php

// This file is generated, manual changes will be lost - read more on https://github.com/algolia/api-clients-automation.

namespace Algolia\AlgoliaSearch\Model\Insights;

/**
 * EventType Class Doc Comment
 *
 * @category Class
 * @package Algolia\AlgoliaSearch
 */
class EventType
{
    /**
     * Possible values of this enum
     */
    const CLICK = 'click';

    const CONVERSION = 'conversion';

    const VIEW = 'view';

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [self::CLICK, self::CONVERSION, self::VIEW];
    }
}
