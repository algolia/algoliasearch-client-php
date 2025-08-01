<?php

// Code generated by OpenAPI Generator (https://openapi-generator.tech), manual changes will be lost - read more on https://github.com/algolia/api-clients-automation. DO NOT EDIT.

namespace Algolia\AlgoliaSearch\Model\AbtestingV3;

use Algolia\AlgoliaSearch\Model\AbstractModel;
use Algolia\AlgoliaSearch\Model\ModelInterface;

/**
 * MetricMetadata Class Doc Comment.
 *
 * @category Class
 *
 * @description Metric specific metadata.
 */
class MetricMetadata extends AbstractModel implements ModelInterface, \ArrayAccess, \JsonSerializable
{
    /**
     * Array of property to type mappings. Used for (de)serialization.
     *
     * @var string[]
     */
    protected static $modelTypes = [
        'winsorizedValue' => 'float',
        'mean' => 'float',
    ];

    /**
     * Array of property to format mappings. Used for (de)serialization.
     *
     * @var string[]
     */
    protected static $modelFormats = [
        'winsorizedValue' => 'double',
        'mean' => 'double',
    ];

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name.
     *
     * @var string[]
     */
    protected static $attributeMap = [
        'winsorizedValue' => 'winsorizedValue',
        'mean' => 'mean',
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses).
     *
     * @var string[]
     */
    protected static $setters = [
        'winsorizedValue' => 'setWinsorizedValue',
        'mean' => 'setMean',
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests).
     *
     * @var string[]
     */
    protected static $getters = [
        'winsorizedValue' => 'getWinsorizedValue',
        'mean' => 'getMean',
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
        if (isset($data['winsorizedValue'])) {
            $this->container['winsorizedValue'] = $data['winsorizedValue'];
        }
        if (isset($data['mean'])) {
            $this->container['mean'] = $data['mean'];
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
        return [];
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
     * Gets winsorizedValue.
     *
     * @return null|float
     */
    public function getWinsorizedValue()
    {
        return $this->container['winsorizedValue'] ?? null;
    }

    /**
     * Sets winsorizedValue.
     *
     * @param null|float $winsorizedValue Only present in case the metric is 'revenue'. It is the amount exceeding the 95th percentile of global revenue transactions involved in the AB Test. This amount is not considered when calculating statistical significance. It is tied to a per revenue-currency pair contrary to other global filter effects (such as outliers and empty search count).
     *
     * @return self
     */
    public function setWinsorizedValue($winsorizedValue)
    {
        $this->container['winsorizedValue'] = $winsorizedValue;

        return $this;
    }

    /**
     * Gets mean.
     *
     * @return null|float
     */
    public function getMean()
    {
        return $this->container['mean'] ?? null;
    }

    /**
     * Sets mean.
     *
     * @param null|float $mean mean value for this metric
     *
     * @return self
     */
    public function setMean($mean)
    {
        $this->container['mean'] = $mean;

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
