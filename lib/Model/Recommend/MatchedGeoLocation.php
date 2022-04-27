<?php

namespace Algolia\AlgoliaSearch\Model\Recommend;

use Algolia\AlgoliaSearch\ObjectSerializer;

/**
 * MatchedGeoLocation Class Doc Comment
 *
 * @category Class
 * @package Algolia\AlgoliaSearch
 */
class MatchedGeoLocation extends \Algolia\AlgoliaSearch\Model\AbstractModel implements
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
        'lat' => 'double',
        'lng' => 'double',
        'distance' => 'int',
    ];

    /**
     * Array of property to format mappings. Used for (de)serialization
     *
     * @var string[]
     */
    protected static $modelFormats = [
        'lat' => 'double',
        'lng' => 'double',
        'distance' => null,
    ];

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
        'lat' => 'setLat',
        'lng' => 'setLng',
        'distance' => 'setDistance',
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'lat' => 'getLat',
        'lng' => 'getLng',
        'distance' => 'getDistance',
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
        if (isset($data['lat'])) {
            $this->container['lat'] = $data['lat'];
        }
        if (isset($data['lng'])) {
            $this->container['lng'] = $data['lng'];
        }
        if (isset($data['distance'])) {
            $this->container['distance'] = $data['distance'];
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
     * Gets lat
     *
     * @return double|null
     */
    public function getLat()
    {
        return $this->container['lat'] ?? null;
    }

    /**
     * Sets lat
     *
     * @param double|null $lat Latitude of the matched location.
     *
     * @return self
     */
    public function setLat($lat)
    {
        $this->container['lat'] = $lat;

        return $this;
    }

    /**
     * Gets lng
     *
     * @return double|null
     */
    public function getLng()
    {
        return $this->container['lng'] ?? null;
    }

    /**
     * Sets lng
     *
     * @param double|null $lng Longitude of the matched location.
     *
     * @return self
     */
    public function setLng($lng)
    {
        $this->container['lng'] = $lng;

        return $this;
    }

    /**
     * Gets distance
     *
     * @return int|null
     */
    public function getDistance()
    {
        return $this->container['distance'] ?? null;
    }

    /**
     * Sets distance
     *
     * @param int|null $distance Distance between the matched location and the search location (in meters).
     *
     * @return self
     */
    public function setDistance($distance)
    {
        $this->container['distance'] = $distance;

        return $this;
    }
    /**
     * Returns true if offset exists. False otherwise.
     *
     * @param integer $offset Offset
     *
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return isset($this->container[$offset]);
    }

    /**
     * Gets offset.
     *
     * @param integer $offset Offset
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
     * @param integer $offset Offset
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->container[$offset]);
    }
}
