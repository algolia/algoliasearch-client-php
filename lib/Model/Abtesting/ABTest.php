<?php

// Code generated by OpenAPI Generator (https://openapi-generator.tech), manual changes will be lost - read more on https://github.com/algolia/api-clients-automation. DO NOT EDIT.

namespace Algolia\AlgoliaSearch\Model\Abtesting;

/**
 * ABTest Class Doc Comment.
 *
 * @category Class
 *
 * @internal
 *
 * @coversNothing
 */
class ABTest extends \Algolia\AlgoliaSearch\Model\AbstractModel implements ModelInterface, \ArrayAccess, \JsonSerializable
{
    /**
     * Array of property to type mappings. Used for (de)serialization.
     *
     * @var string[]
     */
    protected static $modelTypes = [
        'abTestID' => 'int',
        'clickSignificance' => 'float',
        'conversionSignificance' => 'string',
        'updatedAt' => 'string',
        'createdAt' => 'string',
        'name' => 'string',
        'status' => 'string',
        'variants' => '\Algolia\AlgoliaSearch\Model\Abtesting\Variant[]',
    ];

    /**
     * Array of property to format mappings. Used for (de)serialization.
     *
     * @var string[]
     */
    protected static $modelFormats = [
        'abTestID' => null,
        'clickSignificance' => 'double',
        'conversionSignificance' => null,
        'updatedAt' => null,
        'createdAt' => null,
        'name' => null,
        'status' => null,
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
        'clickSignificance' => 'clickSignificance',
        'conversionSignificance' => 'conversionSignificance',
        'updatedAt' => 'updatedAt',
        'createdAt' => 'createdAt',
        'name' => 'name',
        'status' => 'status',
        'variants' => 'variants',
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses).
     *
     * @var string[]
     */
    protected static $setters = [
        'abTestID' => 'setAbTestID',
        'clickSignificance' => 'setClickSignificance',
        'conversionSignificance' => 'setConversionSignificance',
        'updatedAt' => 'setUpdatedAt',
        'createdAt' => 'setCreatedAt',
        'name' => 'setName',
        'status' => 'setStatus',
        'variants' => 'setVariants',
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests).
     *
     * @var string[]
     */
    protected static $getters = [
        'abTestID' => 'getAbTestID',
        'clickSignificance' => 'getClickSignificance',
        'conversionSignificance' => 'getConversionSignificance',
        'updatedAt' => 'getUpdatedAt',
        'createdAt' => 'getCreatedAt',
        'name' => 'getName',
        'status' => 'getStatus',
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
    public function __construct(array $data = null)
    {
        if (isset($data['abTestID'])) {
            $this->container['abTestID'] = $data['abTestID'];
        }
        if (isset($data['clickSignificance'])) {
            $this->container['clickSignificance'] = $data['clickSignificance'];
        }
        if (isset($data['conversionSignificance'])) {
            $this->container['conversionSignificance'] = $data['conversionSignificance'];
        }
        if (isset($data['updatedAt'])) {
            $this->container['updatedAt'] = $data['updatedAt'];
        }
        if (isset($data['createdAt'])) {
            $this->container['createdAt'] = $data['createdAt'];
        }
        if (isset($data['name'])) {
            $this->container['name'] = $data['name'];
        }
        if (isset($data['status'])) {
            $this->container['status'] = $data['status'];
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
        if (!isset($this->container['clickSignificance']) || null === $this->container['clickSignificance']) {
            $invalidProperties[] = "'clickSignificance' can't be null";
        }
        if (!isset($this->container['conversionSignificance']) || null === $this->container['conversionSignificance']) {
            $invalidProperties[] = "'conversionSignificance' can't be null";
        }
        if (!isset($this->container['updatedAt']) || null === $this->container['updatedAt']) {
            $invalidProperties[] = "'updatedAt' can't be null";
        }
        if (!isset($this->container['createdAt']) || null === $this->container['createdAt']) {
            $invalidProperties[] = "'createdAt' can't be null";
        }
        if (!isset($this->container['name']) || null === $this->container['name']) {
            $invalidProperties[] = "'name' can't be null";
        }
        if (!isset($this->container['status']) || null === $this->container['status']) {
            $invalidProperties[] = "'status' can't be null";
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
     * @param int $abTestID unique A/B test ID
     *
     * @return self
     */
    public function setAbTestID($abTestID)
    {
        $this->container['abTestID'] = $abTestID;

        return $this;
    }

    /**
     * Gets clickSignificance.
     *
     * @return float
     */
    public function getClickSignificance()
    {
        return $this->container['clickSignificance'] ?? null;
    }

    /**
     * Sets clickSignificance.
     *
     * @param float $clickSignificance [A/B test significance](https://www.algolia.com/doc/guides/ab-testing/what-is-ab-testing/in-depth/how-ab-test-scores-are-calculated/#statistical-significance-or-chance) based on click data. A value of 0.95 or over is considered to be _significant_.
     *
     * @return self
     */
    public function setClickSignificance($clickSignificance)
    {
        $this->container['clickSignificance'] = $clickSignificance;

        return $this;
    }

    /**
     * Gets conversionSignificance.
     *
     * @return string
     */
    public function getConversionSignificance()
    {
        return $this->container['conversionSignificance'] ?? null;
    }

    /**
     * Sets conversionSignificance.
     *
     * @param string $conversionSignificance End date timestamp in [ISO-8601](https://wikipedia.org/wiki/ISO_8601) format.
     *
     * @return self
     */
    public function setConversionSignificance($conversionSignificance)
    {
        $this->container['conversionSignificance'] = $conversionSignificance;

        return $this;
    }

    /**
     * Gets updatedAt.
     *
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->container['updatedAt'] ?? null;
    }

    /**
     * Sets updatedAt.
     *
     * @param string $updatedAt Update date timestamp in [ISO-8601](https://wikipedia.org/wiki/ISO_8601) format.
     *
     * @return self
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->container['updatedAt'] = $updatedAt;

        return $this;
    }

    /**
     * Gets createdAt.
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->container['createdAt'] ?? null;
    }

    /**
     * Sets createdAt.
     *
     * @param string $createdAt Creation date timestamp in [ISO-8601](https://wikipedia.org/wiki/ISO_8601) format.
     *
     * @return self
     */
    public function setCreatedAt($createdAt)
    {
        $this->container['createdAt'] = $createdAt;

        return $this;
    }

    /**
     * Gets name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->container['name'] ?? null;
    }

    /**
     * Sets name.
     *
     * @param string $name A/B test name
     *
     * @return self
     */
    public function setName($name)
    {
        $this->container['name'] = $name;

        return $this;
    }

    /**
     * Gets status.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->container['status'] ?? null;
    }

    /**
     * Sets status.
     *
     * @param string $status A/B test status
     *
     * @return self
     */
    public function setStatus($status)
    {
        $this->container['status'] = $status;

        return $this;
    }

    /**
     * Gets variants.
     *
     * @return \Algolia\AlgoliaSearch\Model\Abtesting\Variant[]
     */
    public function getVariants()
    {
        return $this->container['variants'] ?? null;
    }

    /**
     * Sets variants.
     *
     * @param \Algolia\AlgoliaSearch\Model\Abtesting\Variant[] $variants A/B test variants
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
     *
     * @return bool
     */
    public function offsetExists($offset)
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
    public function offsetGet($offset)
    {
        return $this->container[$offset] ?? null;
    }

    /**
     * Sets value based on offset.
     *
     * @param null|int $offset Offset
     * @param mixed    $value  Value to be set
     */
    public function offsetSet($offset, $value)
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
    public function offsetUnset($offset)
    {
        unset($this->container[$offset]);
    }
}