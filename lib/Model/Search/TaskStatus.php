<?php

// This file is generated, manual changes will be lost - read more on https://github.com/algolia/api-clients-automation.

namespace Algolia\AlgoliaSearch\Model\Search;

/**
 * TaskStatus Class Doc Comment
 *
 * @category Class
 * @package Algolia\AlgoliaSearch
 */
class TaskStatus
{
    /**
     * Possible values of this enum
     */
    const PUBLISHED = 'published';

    const NOT_PUBLISHED = 'notPublished';

    /**
     * Gets allowable values of the enum
     *
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [self::PUBLISHED, self::NOT_PUBLISHED];
    }
}
