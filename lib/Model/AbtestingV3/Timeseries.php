<?php

// Code generated by OpenAPI Generator (https://openapi-generator.tech), manual changes will be lost - read more on https://github.com/algolia/api-clients-automation. DO NOT EDIT.

namespace Algolia\AlgoliaSearch\Model\AbtestingV3;

use Algolia\AlgoliaSearch\Model\AbstractModel;
use Algolia\AlgoliaSearch\Model\ModelInterface;

/**
 * Timeseries Class Doc Comment.
 *
 * @category Class
 */
class Timeseries extends AbstractModel implements ModelInterface, \ArrayAccess, \JsonSerializable
{
    /**
     * Array of property to type mappings. Used for (de)serialization.
     *
     * @var string[]
     */
    protected static $modelTypes = [
        'abTestID' => 'int',
        'variants' => '\Algolia\AlgoliaSearch\Model\AbtestingV3\TimeseriesVariant[]',
    ];

    /**
     * Array of property to format mappings. Used for (de)serialization.
     *
     * @var string[]
     */
    protected static $modelFormats = [
        'abTestID' => null,
        'variants' => null,
    ];

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name.
     *
     * @var string[]
     */
    protected static $attributeMap = [
        'abTestID' => 'abTestID',
        'variants' => 'variants',
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses).
     *
     * @var string[]
     */
    protected static $setters = [
        'abTestID' => 'setAbTestID',
        'variants' => 'setVariants',
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests).
     *
     * @var string[]
     */
    protected static $getters = [
        'abTestID' => 'getAbTestID',
        'variants' => 'getVariants',
    ];

    /**
     * Associative array for storing property values.
     *
     * @var mixed[]
     */
    protected $container = [];

    /**
     * Constructor.
     *
     * @param mixed[] $data Associated array of property values
     */
    public function __construct(?array $data = null)
    {
        if (isset($data['abTestID'])) {
            $this->container['abTestID'] = $data['abTestID'];
        }
        if (isset($data['variants'])) {
            $this->container['variants'] = $data['variants'];
        }
    }

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name.
     *
     * @return array
     */
    public static function attributeMap()
    {
        return self::$attributeMap;
    }

    /**
     * Array of property to type mappings. Used for (de)serialization.
     *
     * @return array
     */
    public static function modelTypes()
    {
        return self::$modelTypes;
    }

    /**
     * Array of property to format mappings. Used for (de)serialization.
     *
     * @return array
     */
    public static function modelFormats()
    {
        return self::$modelFormats;
    }

    /**
     * Array of attributes to setter functions (for deserialization of responses).
     *
     * @return array
     */
    public static function setters()
    {
        return self::$setters;
    }

    /**
     * Array of attributes to getter functions (for serialization of requests).
     *
     * @return array
     */
    public static function getters()
    {
        return self::$getters;
    }

    /**
     * Show all the invalid properties with reasons.
     *
     * @return array invalid properties with reasons
     */
    public function listInvalidProperties()
    {
        $invalidProperties = [];

        if (!isset($this->container['abTestID']) || null === $this->container['abTestID']) {
            $invalidProperties[] = "'abTestID' can't be null";
        }
        if (!isset($this->container['variants']) || null === $this->container['variants']) {
            $invalidProperties[] = "'variants' can't be null";
        }

        return $invalidProperties;
    }

    /**
     * Validate all the properties in the model
     * return true if all passed.
     *
     * @return bool True if all properties are valid
     */
    public function valid()
    {
        return 0 === count($this->listInvalidProperties());
    }

    /**
     * Gets abTestID.
     *
     * @return int
     */
    public function getAbTestID()
    {
        return $this->container['abTestID'] ?? null;
    }

    /**
     * Sets abTestID.
     *
     * @param int $abTestID unique A/B test identifier
     *
     * @return self
     */
    public function setAbTestID($abTestID)
    {
        $this->container['abTestID'] = $abTestID;

        return $this;
    }

    /**
     * Gets variants.
     *
     * @return TimeseriesVariant[]
     */
    public function getVariants()
    {
        return $this->container['variants'] ?? null;
    }

    /**
     * Sets variants.
     *
     * @param TimeseriesVariant[] $variants A/B test timeseries variants.  The first variant is your _control_ index, typically your production index. All of the additional variants are indexes with changed settings that you want to test against the control.
     *
     * @return self
     */
    public function setVariants($variants)
    {
        $this->container['variants'] = $variants;

        return $this;
    }

    /**
     * Returns true if offset exists. False otherwise.
     *
     * @param int $offset Offset
     */
    public function offsetExists($offset): bool
    {
        return isset($this->container[$offset]);
    }

    /**
     * Gets offset.
     *
     * @param int $offset Offset
     *
     * @return null|mixed
     */
    public function offsetGet($offset): mixed
    {
        return $this->container[$offset] ?? null;
    }

    /**
     * Sets value based on offset.
     *
     * @param null|int $offset Offset
     * @param mixed    $value  Value to be set
     */
    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    /**
     * Unsets offset.
     *
     * @param int $offset Offset
     */
    public function offsetUnset($offset): void
    {
        unset($this->container[$offset]);
    }
}
