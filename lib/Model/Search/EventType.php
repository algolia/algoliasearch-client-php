<?php

// Code generated by OpenAPI Generator (https://openapi-generator.tech), manual changes will be lost - read more on https://github.com/algolia/api-clients-automation. DO NOT EDIT.

namespace Algolia\AlgoliaSearch\Model\Search;

/**
 * EventType Class Doc Comment.
 *
 * @category Class
 */
class EventType
{
    /**
     * Possible values of this enum.
     */
    public const FETCH = 'fetch';

    public const RECORD = 'record';

    public const LOG = 'log';

    public const TRANSFORM = 'transform';

    /**
     * Gets allowable values of the enum.
     *
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [
            self::FETCH,
            self::RECORD,
            self::LOG,
            self::TRANSFORM,
        ];
    }
}
