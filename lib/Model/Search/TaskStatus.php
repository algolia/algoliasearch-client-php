<?php

namespace Algolia\AlgoliaSearch\Model\Search;

use Algolia\AlgoliaSearch\ObjectSerializer;

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
    public const PUBLISHED = 'published';

    public const NOT_PUBLISHED = 'notPublished';

    /**
     * Gets allowable values of the enum
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [self::PUBLISHED, self::NOT_PUBLISHED];
    }
}
