<?php

// Code generated by OpenAPI Generator (https://openapi-generator.tech), manual changes will be lost - read more on https://github.com/algolia/api-clients-automation. DO NOT EDIT.

namespace Algolia\AlgoliaSearch\Model\Ingestion;

use Algolia\AlgoliaSearch\Model\AbstractModel;
use Algolia\AlgoliaSearch\Model\ModelInterface;

/**
 * Transformation Class Doc Comment.
 *
 * @category Class
 */
class Transformation extends AbstractModel implements ModelInterface, \ArrayAccess, \JsonSerializable
{
    /**
     * Array of property to type mappings. Used for (de)serialization.
     *
     * @var string[]
     */
    protected static $modelTypes = [
        'transformationID' => 'string',
        'authenticationIDs' => 'string[]',
        'code' => 'string',
        'type' => '\Algolia\AlgoliaSearch\Model\Ingestion\TransformationType',
        'input' => '\Algolia\AlgoliaSearch\Model\Ingestion\TransformationInput',
        'name' => 'string',
        'description' => 'string',
        'owner' => 'string',
        'createdAt' => 'string',
        'updatedAt' => 'string',
    ];

    /**
     * Array of property to format mappings. Used for (de)serialization.
     *
     * @var string[]
     */
    protected static $modelFormats = [
        'transformationID' => null,
        'authenticationIDs' => null,
        'code' => null,
        'type' => null,
        'input' => null,
        'name' => null,
        'description' => null,
        'owner' => null,
        'createdAt' => null,
        'updatedAt' => null,
    ];

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name.
     *
     * @var string[]
     */
    protected static $attributeMap = [
        'transformationID' => 'transformationID',
        'authenticationIDs' => 'authenticationIDs',
        'code' => 'code',
        'type' => 'type',
        'input' => 'input',
        'name' => 'name',
        'description' => 'description',
        'owner' => 'owner',
        'createdAt' => 'createdAt',
        'updatedAt' => 'updatedAt',
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses).
     *
     * @var string[]
     */
    protected static $setters = [
        'transformationID' => 'setTransformationID',
        'authenticationIDs' => 'setAuthenticationIDs',
        'code' => 'setCode',
        'type' => 'setType',
        'input' => 'setInput',
        'name' => 'setName',
        'description' => 'setDescription',
        'owner' => 'setOwner',
        'createdAt' => 'setCreatedAt',
        'updatedAt' => 'setUpdatedAt',
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests).
     *
     * @var string[]
     */
    protected static $getters = [
        'transformationID' => 'getTransformationID',
        'authenticationIDs' => 'getAuthenticationIDs',
        'code' => 'getCode',
        'type' => 'getType',
        'input' => 'getInput',
        'name' => 'getName',
        'description' => 'getDescription',
        'owner' => 'getOwner',
        'createdAt' => 'getCreatedAt',
        'updatedAt' => 'getUpdatedAt',
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
        if (isset($data['transformationID'])) {
            $this->container['transformationID'] = $data['transformationID'];
        }
        if (isset($data['authenticationIDs'])) {
            $this->container['authenticationIDs'] = $data['authenticationIDs'];
        }
        if (isset($data['code'])) {
            $this->container['code'] = $data['code'];
        }
        if (isset($data['type'])) {
            $this->container['type'] = $data['type'];
        }
        if (isset($data['input'])) {
            $this->container['input'] = $data['input'];
        }
        if (isset($data['name'])) {
            $this->container['name'] = $data['name'];
        }
        if (isset($data['description'])) {
            $this->container['description'] = $data['description'];
        }
        if (isset($data['owner'])) {
            $this->container['owner'] = $data['owner'];
        }
        if (isset($data['createdAt'])) {
            $this->container['createdAt'] = $data['createdAt'];
        }
        if (isset($data['updatedAt'])) {
            $this->container['updatedAt'] = $data['updatedAt'];
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

        if (!isset($this->container['transformationID']) || null === $this->container['transformationID']) {
            $invalidProperties[] = "'transformationID' can't be null";
        }
        if (!isset($this->container['code']) || null === $this->container['code']) {
            $invalidProperties[] = "'code' can't be null";
        }
        if (!isset($this->container['name']) || null === $this->container['name']) {
            $invalidProperties[] = "'name' can't be null";
        }
        if (!isset($this->container['createdAt']) || null === $this->container['createdAt']) {
            $invalidProperties[] = "'createdAt' can't be null";
        }
        if (!isset($this->container['updatedAt']) || null === $this->container['updatedAt']) {
            $invalidProperties[] = "'updatedAt' can't be null";
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
     * Gets transformationID.
     *
     * @return string
     */
    public function getTransformationID()
    {
        return $this->container['transformationID'] ?? null;
    }

    /**
     * Sets transformationID.
     *
     * @param string $transformationID universally unique identifier (UUID) of a transformation
     *
     * @return self
     */
    public function setTransformationID($transformationID)
    {
        $this->container['transformationID'] = $transformationID;

        return $this;
    }

    /**
     * Gets authenticationIDs.
     *
     * @return null|string[]
     */
    public function getAuthenticationIDs()
    {
        return $this->container['authenticationIDs'] ?? null;
    }

    /**
     * Sets authenticationIDs.
     *
     * @param null|string[] $authenticationIDs the authentications associated with the current transformation
     *
     * @return self
     */
    public function setAuthenticationIDs($authenticationIDs)
    {
        $this->container['authenticationIDs'] = $authenticationIDs;

        return $this;
    }

    /**
     * Gets code.
     *
     * @return string
     *
     * @deprecated
     */
    public function getCode()
    {
        return $this->container['code'] ?? null;
    }

    /**
     * Sets code.
     *
     * @param string $code It is deprecated. Use the `input` field with proper `type` instead to specify the transformation code.
     *
     * @return self
     *
     * @deprecated
     */
    public function setCode($code)
    {
        $this->container['code'] = $code;

        return $this;
    }

    /**
     * Gets type.
     *
     * @return null|TransformationType
     */
    public function getType()
    {
        return $this->container['type'] ?? null;
    }

    /**
     * Sets type.
     *
     * @param null|TransformationType $type type
     *
     * @return self
     */
    public function setType($type)
    {
        $this->container['type'] = $type;

        return $this;
    }

    /**
     * Gets input.
     *
     * @return null|TransformationInput
     */
    public function getInput()
    {
        return $this->container['input'] ?? null;
    }

    /**
     * Sets input.
     *
     * @param null|TransformationInput $input input
     *
     * @return self
     */
    public function setInput($input)
    {
        $this->container['input'] = $input;

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
     * @param string $name the uniquely identified name of your transformation
     *
     * @return self
     */
    public function setName($name)
    {
        $this->container['name'] = $name;

        return $this;
    }

    /**
     * Gets description.
     *
     * @return null|string
     */
    public function getDescription()
    {
        return $this->container['description'] ?? null;
    }

    /**
     * Sets description.
     *
     * @param null|string $description a descriptive name for your transformation of what it does
     *
     * @return self
     */
    public function setDescription($description)
    {
        $this->container['description'] = $description;

        return $this;
    }

    /**
     * Gets owner.
     *
     * @return null|string
     */
    public function getOwner()
    {
        return $this->container['owner'] ?? null;
    }

    /**
     * Sets owner.
     *
     * @param null|string $owner owner of the resource
     *
     * @return self
     */
    public function setOwner($owner)
    {
        $this->container['owner'] = $owner;

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
     * @param string $createdAt date of creation in RFC 3339 format
     *
     * @return self
     */
    public function setCreatedAt($createdAt)
    {
        $this->container['createdAt'] = $createdAt;

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
     * @param string $updatedAt date of last update in RFC 3339 format
     *
     * @return self
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->container['updatedAt'] = $updatedAt;

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
