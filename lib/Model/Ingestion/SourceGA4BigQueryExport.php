<?php

// Code generated by OpenAPI Generator (https://openapi-generator.tech), manual changes will be lost - read more on https://github.com/algolia/api-clients-automation. DO NOT EDIT.

namespace Algolia\AlgoliaSearch\Model\Ingestion;

/**
 * SourceGA4BigQueryExport Class Doc Comment.
 *
 * @category Class
 */
class SourceGA4BigQueryExport extends \Algolia\AlgoliaSearch\Model\AbstractModel implements ModelInterface, \ArrayAccess, \JsonSerializable
{
    /**
     * Array of property to type mappings. Used for (de)serialization.
     *
     * @var string[]
     */
    protected static $modelTypes = [
        'projectID' => 'string',
        'datasetID' => 'string',
        'tablePrefix' => 'string',
    ];

    /**
     * Array of property to format mappings. Used for (de)serialization.
     *
     * @var string[]
     */
    protected static $modelFormats = [
        'projectID' => null,
        'datasetID' => null,
        'tablePrefix' => null,
    ];

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name.
     *
     * @var string[]
     */
    protected static $attributeMap = [
        'projectID' => 'projectID',
        'datasetID' => 'datasetID',
        'tablePrefix' => 'tablePrefix',
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses).
     *
     * @var string[]
     */
    protected static $setters = [
        'projectID' => 'setProjectID',
        'datasetID' => 'setDatasetID',
        'tablePrefix' => 'setTablePrefix',
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests).
     *
     * @var string[]
     */
    protected static $getters = [
        'projectID' => 'getProjectID',
        'datasetID' => 'getDatasetID',
        'tablePrefix' => 'getTablePrefix',
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
        if (isset($data['projectID'])) {
            $this->container['projectID'] = $data['projectID'];
        }
        if (isset($data['datasetID'])) {
            $this->container['datasetID'] = $data['datasetID'];
        }
        if (isset($data['tablePrefix'])) {
            $this->container['tablePrefix'] = $data['tablePrefix'];
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

        if (!isset($this->container['projectID']) || null === $this->container['projectID']) {
            $invalidProperties[] = "'projectID' can't be null";
        }
        if (!isset($this->container['datasetID']) || null === $this->container['datasetID']) {
            $invalidProperties[] = "'datasetID' can't be null";
        }
        if (!isset($this->container['tablePrefix']) || null === $this->container['tablePrefix']) {
            $invalidProperties[] = "'tablePrefix' can't be null";
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
     * Gets projectID.
     *
     * @return string
     */
    public function getProjectID()
    {
        return $this->container['projectID'] ?? null;
    }

    /**
     * Sets projectID.
     *
     * @param string $projectID GCP project ID that the BigQuery Export writes to
     *
     * @return self
     */
    public function setProjectID($projectID)
    {
        $this->container['projectID'] = $projectID;

        return $this;
    }

    /**
     * Gets datasetID.
     *
     * @return string
     */
    public function getDatasetID()
    {
        return $this->container['datasetID'] ?? null;
    }

    /**
     * Sets datasetID.
     *
     * @param string $datasetID bigQuery dataset ID that the BigQuery Export writes to
     *
     * @return self
     */
    public function setDatasetID($datasetID)
    {
        $this->container['datasetID'] = $datasetID;

        return $this;
    }

    /**
     * Gets tablePrefix.
     *
     * @return string
     */
    public function getTablePrefix()
    {
        return $this->container['tablePrefix'] ?? null;
    }

    /**
     * Sets tablePrefix.
     *
     * @param string $tablePrefix Prefix of the tables that the BigQuery Export writes to (i.e. events_intraday_ for streaming, events_ for daily).
     *
     * @return self
     */
    public function setTablePrefix($tablePrefix)
    {
        $this->container['tablePrefix'] = $tablePrefix;

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