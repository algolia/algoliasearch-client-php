<?php

// This file is generated, manual changes will be lost - read more on https://github.com/algolia/api-clients-automation.

namespace Algolia\AlgoliaSearch\Model\Recommend;

/**
 * RecommendationsRequest Class Doc Comment
 *
 * @category Class
 * @package Algolia\AlgoliaSearch
 */
class RecommendationsRequest extends \Algolia\AlgoliaSearch\Model\AbstractModel implements
    ModelInterface,
    \ArrayAccess,
    \JsonSerializable
{
    /**
     * Array of property to type mappings. Used for (de)serialization
     *
     * @var string[]
     */
    protected static $modelTypes = [
        'model' => '\Algolia\AlgoliaSearch\Model\Recommend\RecommendationModels',
        'facetName' => 'string',
        'facetValue' => 'string',
        'indexName' => 'string',
        'threshold' => 'int',
        'maxRecommendations' => 'int',
        'queryParameters' => '\Algolia\AlgoliaSearch\Model\Recommend\SearchParamsObject',
        'fallbackParameters' => '\Algolia\AlgoliaSearch\Model\Recommend\SearchParamsObject',
        'objectID' => 'string',
    ];

    /**
     * Array of property to format mappings. Used for (de)serialization
     *
     * @var string[]
     */
    protected static $modelFormats = [
        'model' => null,
        'facetName' => null,
        'facetValue' => null,
        'indexName' => null,
        'threshold' => null,
        'maxRecommendations' => null,
        'queryParameters' => null,
        'fallbackParameters' => null,
        'objectID' => null,
    ];

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name
     *
     * @var string[]
     */
    protected static $attributeMap = [
        'model' => 'model',
        'facetName' => 'facetName',
        'facetValue' => 'facetValue',
        'indexName' => 'indexName',
        'threshold' => 'threshold',
        'maxRecommendations' => 'maxRecommendations',
        'queryParameters' => 'queryParameters',
        'fallbackParameters' => 'fallbackParameters',
        'objectID' => 'objectID',
    ];

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name
     *
     * @return array
     */
    public static function attributeMap()
    {
        return self::$attributeMap;
    }

    /**
     * Array of property to type mappings. Used for (de)serialization
     *
     * @return array
     */
    public static function modelTypes()
    {
        return self::$modelTypes;
    }

    /**
     * Array of property to format mappings. Used for (de)serialization
     *
     * @return array
     */
    public static function modelFormats()
    {
        return self::$modelFormats;
    }

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'model' => 'setModel',
        'facetName' => 'setFacetName',
        'facetValue' => 'setFacetValue',
        'indexName' => 'setIndexName',
        'threshold' => 'setThreshold',
        'maxRecommendations' => 'setMaxRecommendations',
        'queryParameters' => 'setQueryParameters',
        'fallbackParameters' => 'setFallbackParameters',
        'objectID' => 'setObjectID',
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'model' => 'getModel',
        'facetName' => 'getFacetName',
        'facetValue' => 'getFacetValue',
        'indexName' => 'getIndexName',
        'threshold' => 'getThreshold',
        'maxRecommendations' => 'getMaxRecommendations',
        'queryParameters' => 'getQueryParameters',
        'fallbackParameters' => 'getFallbackParameters',
        'objectID' => 'getObjectID',
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @return array
     */
    public static function setters()
    {
        return self::$setters;
    }

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @return array
     */
    public static function getters()
    {
        return self::$getters;
    }

    /**
     * Associative array for storing property values
     *
     * @var mixed[]
     */
    protected $container = [];

    /**
     * Constructor
     *
     * @param mixed[] $data Associated array of property values
     */
    public function __construct(array $data = null)
    {
        if (isset($data['model'])) {
            $this->container['model'] = $data['model'];
        }
        if (isset($data['facetName'])) {
            $this->container['facetName'] = $data['facetName'];
        }
        if (isset($data['facetValue'])) {
            $this->container['facetValue'] = $data['facetValue'];
        }
        if (isset($data['indexName'])) {
            $this->container['indexName'] = $data['indexName'];
        }
        if (isset($data['threshold'])) {
            $this->container['threshold'] = $data['threshold'];
        }
        if (isset($data['maxRecommendations'])) {
            $this->container['maxRecommendations'] =
                $data['maxRecommendations'];
        }
        if (isset($data['queryParameters'])) {
            $this->container['queryParameters'] = $data['queryParameters'];
        }
        if (isset($data['fallbackParameters'])) {
            $this->container['fallbackParameters'] =
                $data['fallbackParameters'];
        }
        if (isset($data['objectID'])) {
            $this->container['objectID'] = $data['objectID'];
        }
    }

    /**
     * Show all the invalid properties with reasons.
     *
     * @return array invalid properties with reasons
     */
    public function listInvalidProperties()
    {
        $invalidProperties = [];

        if (
            !isset($this->container['model']) ||
            $this->container['model'] === null
        ) {
            $invalidProperties[] = "'model' can't be null";
        }
        if (
            !isset($this->container['indexName']) ||
            $this->container['indexName'] === null
        ) {
            $invalidProperties[] = "'indexName' can't be null";
        }
        if (
            !isset($this->container['threshold']) ||
            $this->container['threshold'] === null
        ) {
            $invalidProperties[] = "'threshold' can't be null";
        }
        if ($this->container['threshold'] > 100) {
            $invalidProperties[] =
                "invalid value for 'threshold', must be smaller than or equal to 100.";
        }

        if ($this->container['threshold'] < 0) {
            $invalidProperties[] =
                "invalid value for 'threshold', must be bigger than or equal to 0.";
        }

        if (
            !isset($this->container['objectID']) ||
            $this->container['objectID'] === null
        ) {
            $invalidProperties[] = "'objectID' can't be null";
        }

        return $invalidProperties;
    }

    /**
     * Validate all the properties in the model
     * return true if all passed
     *
     * @return bool True if all properties are valid
     */
    public function valid()
    {
        return count($this->listInvalidProperties()) === 0;
    }

    /**
     * Gets model
     *
     * @return \Algolia\AlgoliaSearch\Model\Recommend\RecommendationModels
     */
    public function getModel()
    {
        return $this->container['model'] ?? null;
    }

    /**
     * Sets model
     *
     * @param \Algolia\AlgoliaSearch\Model\Recommend\RecommendationModels $model model
     *
     * @return self
     */
    public function setModel($model)
    {
        $this->container['model'] = $model;

        return $this;
    }

    /**
     * Gets facetName
     *
     * @return string|null
     */
    public function getFacetName()
    {
        return $this->container['facetName'] ?? null;
    }

    /**
     * Sets facetName
     *
     * @param string|null $facetName the facet name to use for trending models
     *
     * @return self
     */
    public function setFacetName($facetName)
    {
        $this->container['facetName'] = $facetName;

        return $this;
    }

    /**
     * Gets facetValue
     *
     * @return string|null
     */
    public function getFacetValue()
    {
        return $this->container['facetValue'] ?? null;
    }

    /**
     * Sets facetValue
     *
     * @param string|null $facetValue the facet value to use for trending models
     *
     * @return self
     */
    public function setFacetValue($facetValue)
    {
        $this->container['facetValue'] = $facetValue;

        return $this;
    }

    /**
     * Gets indexName
     *
     * @return string
     */
    public function getIndexName()
    {
        return $this->container['indexName'] ?? null;
    }

    /**
     * Sets indexName
     *
     * @param string $indexName the Algolia index name
     *
     * @return self
     */
    public function setIndexName($indexName)
    {
        $this->container['indexName'] = $indexName;

        return $this;
    }

    /**
     * Gets threshold
     *
     * @return int
     */
    public function getThreshold()
    {
        return $this->container['threshold'] ?? null;
    }

    /**
     * Sets threshold
     *
     * @param int $threshold the threshold to use when filtering recommendations by their score
     *
     * @return self
     */
    public function setThreshold($threshold)
    {
        if ($threshold > 100) {
            throw new \InvalidArgumentException(
                'invalid value for $threshold when calling RecommendationsRequest., must be smaller than or equal to 100.'
            );
        }
        if ($threshold < 0) {
            throw new \InvalidArgumentException(
                'invalid value for $threshold when calling RecommendationsRequest., must be bigger than or equal to 0.'
            );
        }

        $this->container['threshold'] = $threshold;

        return $this;
    }

    /**
     * Gets maxRecommendations
     *
     * @return int|null
     */
    public function getMaxRecommendations()
    {
        return $this->container['maxRecommendations'] ?? null;
    }

    /**
     * Sets maxRecommendations
     *
     * @param int|null $maxRecommendations The max number of recommendations to retrieve. If it's set to 0, all the recommendations of the objectID may be returned.
     *
     * @return self
     */
    public function setMaxRecommendations($maxRecommendations)
    {
        $this->container['maxRecommendations'] = $maxRecommendations;

        return $this;
    }

    /**
     * Gets queryParameters
     *
     * @return \Algolia\AlgoliaSearch\Model\Recommend\SearchParamsObject|null
     */
    public function getQueryParameters()
    {
        return $this->container['queryParameters'] ?? null;
    }

    /**
     * Sets queryParameters
     *
     * @param \Algolia\AlgoliaSearch\Model\Recommend\SearchParamsObject|null $queryParameters queryParameters
     *
     * @return self
     */
    public function setQueryParameters($queryParameters)
    {
        $this->container['queryParameters'] = $queryParameters;

        return $this;
    }

    /**
     * Gets fallbackParameters
     *
     * @return \Algolia\AlgoliaSearch\Model\Recommend\SearchParamsObject|null
     */
    public function getFallbackParameters()
    {
        return $this->container['fallbackParameters'] ?? null;
    }

    /**
     * Sets fallbackParameters
     *
     * @param \Algolia\AlgoliaSearch\Model\Recommend\SearchParamsObject|null $fallbackParameters fallbackParameters
     *
     * @return self
     */
    public function setFallbackParameters($fallbackParameters)
    {
        $this->container['fallbackParameters'] = $fallbackParameters;

        return $this;
    }

    /**
     * Gets objectID
     *
     * @return string
     */
    public function getObjectID()
    {
        return $this->container['objectID'] ?? null;
    }

    /**
     * Sets objectID
     *
     * @param string $objectID unique identifier of the object
     *
     * @return self
     */
    public function setObjectID($objectID)
    {
        $this->container['objectID'] = $objectID;

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
     * @return mixed|null
     */
    public function offsetGet($offset)
    {
        return $this->container[$offset] ?? null;
    }

    /**
     * Sets value based on offset.
     *
     * @param int|null $offset Offset
     * @param mixed    $value  Value to be set
     *
     * @return void
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
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->container[$offset]);
    }
}
