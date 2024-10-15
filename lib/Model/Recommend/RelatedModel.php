<?php

// Code generated by OpenAPI Generator (https://openapi-generator.tech), manual changes will be lost - read more on https://github.com/algolia/api-clients-automation. DO NOT EDIT.

namespace Algolia\AlgoliaSearch\Model\Recommend;

/**
 * RelatedModel Class Doc Comment.
 *
 * @category Class
 *
 * @description Related products or similar content model.  This model recommends items that are similar to the item with the ID `objectID`. Similarity is determined from the user interactions and attributes.
 */
class RelatedModel
{
    /**
     * Possible values of this enum.
     */
    public const RELATED_PRODUCTS = 'related-products';

    /**
     * Gets allowable values of the enum.
     *
     * @return string[]
     */
    public static function getAllowableEnumValues()
    {
        return [
            self::RELATED_PRODUCTS,
        ];
    }
}
