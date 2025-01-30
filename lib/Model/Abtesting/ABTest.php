<?php

// Code generated by OpenAPI Generator (https://openapi-generator.tech), manual changes will be lost - read more on https://github.com/algolia/api-clients-automation. DO NOT EDIT.

namespace Algolia\AlgoliaSearch\Model\Abtesting;

use Algolia\AlgoliaSearch\Model\AbstractModel;
use Algolia\AlgoliaSearch\Model\ModelInterface;

/**
 * ABTest Class Doc Comment.
 *
 * @category Class
 *
 * @internal
 *
 * @coversNothing
 */
class ABTest extends AbstractModel implements ModelInterface, \ArrayAccess, \JsonSerializable
{
    /**
     * Array of property to type mappings. Used for (de)serialization.
     *
     * @var string[]
     */
    protected static $modelTypes = [
        'abTestID' => 'int',
        'clickSignificance' => 'float',
        'conversionSignificance' => 'float',
        'addToCartSignificance' => 'float',
        'purchaseSignificance' => 'float',
        'revenueSignificance' => 'array<string,float>',
        'updatedAt' => 'string',
        'createdAt' => 'string',
        'endAt' => 'string',
        'name' => 'string',
        'status' => '\Algolia\AlgoliaSearch\Model\Abtesting\Status',
        'variants' => '\Algolia\AlgoliaSearch\Model\Abtesting\Variant[]',
        'configuration' => '\Algolia\AlgoliaSearch\Model\Abtesting\ABTestConfiguration',
    ];

    /**
     * Array of property to format mappings. Used for (de)serialization.
     *
     * @var string[]
     */
    protected static $modelFormats = [
        'abTestID' => null,
        'clickSignificance' => 'double',
        'conversionSignificance' => 'double',
        'addToCartSignificance' => 'double',
        'purchaseSignificance' => 'double',
        'revenueSignificance' => 'double',
        'updatedAt' => null,
        'createdAt' => null,
        'endAt' => null,
        'name' => null,
        'status' => null,
        'variants' => null,
        'configuration' => null,
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
        'addToCartSignificance' => 'addToCartSignificance',
        'purchaseSignificance' => 'purchaseSignificance',
        'revenueSignificance' => 'revenueSignificance',
        'updatedAt' => 'updatedAt',
        'createdAt' => 'createdAt',
        'endAt' => 'endAt',
        'name' => 'name',
        'status' => 'status',
        'variants' => 'variants',
        'configuration' => 'configuration',
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
        'addToCartSignificance' => 'setAddToCartSignificance',
        'purchaseSignificance' => 'setPurchaseSignificance',
        'revenueSignificance' => 'setRevenueSignificance',
        'updatedAt' => 'setUpdatedAt',
        'createdAt' => 'setCreatedAt',
        'endAt' => 'setEndAt',
        'name' => 'setName',
        'status' => 'setStatus',
        'variants' => 'setVariants',
        'configuration' => 'setConfiguration',
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
        'addToCartSignificance' => 'getAddToCartSignificance',
        'purchaseSignificance' => 'getPurchaseSignificance',
        'revenueSignificance' => 'getRevenueSignificance',
        'updatedAt' => 'getUpdatedAt',
        'createdAt' => 'getCreatedAt',
        'endAt' => 'getEndAt',
        'name' => 'getName',
        'status' => 'getStatus',
        'variants' => 'getVariants',
        'configuration' => 'getConfiguration',
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
        if (isset($data['clickSignificance'])) {
            $this->container['clickSignificance'] = $data['clickSignificance'];
        }
        if (isset($data['conversionSignificance'])) {
            $this->container['conversionSignificance'] = $data['conversionSignificance'];
        }
        if (isset($data['addToCartSignificance'])) {
            $this->container['addToCartSignificance'] = $data['addToCartSignificance'];
        }
        if (isset($data['purchaseSignificance'])) {
            $this->container['purchaseSignificance'] = $data['purchaseSignificance'];
        }
        if (isset($data['revenueSignificance'])) {
            $this->container['revenueSignificance'] = $data['revenueSignificance'];
        }
        if (isset($data['updatedAt'])) {
            $this->container['updatedAt'] = $data['updatedAt'];
        }
        if (isset($data['createdAt'])) {
            $this->container['createdAt'] = $data['createdAt'];
        }
        if (isset($data['endAt'])) {
            $this->container['endAt'] = $data['endAt'];
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
        if (isset($data['configuration'])) {
            $this->container['configuration'] = $data['configuration'];
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
        if (!isset($this->container['updatedAt']) || null === $this->container['updatedAt']) {
            $invalidProperties[] = "'updatedAt' can't be null";
        }
        if (!isset($this->container['createdAt']) || null === $this->container['createdAt']) {
            $invalidProperties[] = "'createdAt' can't be null";
        }
        if (!isset($this->container['endAt']) || null === $this->container['endAt']) {
            $invalidProperties[] = "'endAt' can't be null";
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
     * Gets clickSignificance.
     *
     * @return null|float
     */
    public function getClickSignificance()
    {
        return $this->container['clickSignificance'] ?? null;
    }

    /**
     * Sets clickSignificance.
     *
     * @param null|float $clickSignificance clickSignificance
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
     * @return null|float
     */
    public function getConversionSignificance()
    {
        return $this->container['conversionSignificance'] ?? null;
    }

    /**
     * Sets conversionSignificance.
     *
     * @param null|float $conversionSignificance conversionSignificance
     *
     * @return self
     */
    public function setConversionSignificance($conversionSignificance)
    {
        $this->container['conversionSignificance'] = $conversionSignificance;

        return $this;
    }

    /**
     * Gets addToCartSignificance.
     *
     * @return null|float
     */
    public function getAddToCartSignificance()
    {
        return $this->container['addToCartSignificance'] ?? null;
    }

    /**
     * Sets addToCartSignificance.
     *
     * @param null|float $addToCartSignificance addToCartSignificance
     *
     * @return self
     */
    public function setAddToCartSignificance($addToCartSignificance)
    {
        $this->container['addToCartSignificance'] = $addToCartSignificance;

        return $this;
    }

    /**
     * Gets purchaseSignificance.
     *
     * @return null|float
     */
    public function getPurchaseSignificance()
    {
        return $this->container['purchaseSignificance'] ?? null;
    }

    /**
     * Sets purchaseSignificance.
     *
     * @param null|float $purchaseSignificance purchaseSignificance
     *
     * @return self
     */
    public function setPurchaseSignificance($purchaseSignificance)
    {
        $this->container['purchaseSignificance'] = $purchaseSignificance;

        return $this;
    }

    /**
     * Gets revenueSignificance.
     *
     * @return null|array<string,float>
     */
    public function getRevenueSignificance()
    {
        return $this->container['revenueSignificance'] ?? null;
    }

    /**
     * Sets revenueSignificance.
     *
     * @param null|array<string,float> $revenueSignificance revenueSignificance
     *
     * @return self
     */
    public function setRevenueSignificance($revenueSignificance)
    {
        $this->container['revenueSignificance'] = $revenueSignificance;

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
     * @param string $updatedAt date and time when the A/B test was last updated, in RFC 3339 format
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
     * @param string $createdAt date and time when the A/B test was created, in RFC 3339 format
     *
     * @return self
     */
    public function setCreatedAt($createdAt)
    {
        $this->container['createdAt'] = $createdAt;

        return $this;
    }

    /**
     * Gets endAt.
     *
     * @return string
     */
    public function getEndAt()
    {
        return $this->container['endAt'] ?? null;
    }

    /**
     * Sets endAt.
     *
     * @param string $endAt end date and time of the A/B test, in RFC 3339 format
     *
     * @return self
     */
    public function setEndAt($endAt)
    {
        $this->container['endAt'] = $endAt;

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
     * @return Status
     */
    public function getStatus()
    {
        return $this->container['status'] ?? null;
    }

    /**
     * Sets status.
     *
     * @param Status $status status
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
     * @return Variant[]
     */
    public function getVariants()
    {
        return $this->container['variants'] ?? null;
    }

    /**
     * Sets variants.
     *
     * @param Variant[] $variants A/B test variants.  The first variant is your _control_ index, typically your production index. The second variant is an index with changed settings that you want to test against the control.
     *
     * @return self
     */
    public function setVariants($variants)
    {
        $this->container['variants'] = $variants;

        return $this;
    }

    /**
     * Gets configuration.
     *
     * @return null|ABTestConfiguration
     */
    public function getConfiguration()
    {
        return $this->container['configuration'] ?? null;
    }

    /**
     * Sets configuration.
     *
     * @param null|ABTestConfiguration $configuration configuration
     *
     * @return self
     */
    public function setConfiguration($configuration)
    {
        $this->container['configuration'] = $configuration;

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
