<?php

// Code generated by OpenAPI Generator (https://openapi-generator.tech), manual changes will be lost - read more on https://github.com/algolia/api-clients-automation. DO NOT EDIT.

namespace Algolia\AlgoliaSearch\Model\Search;

use Algolia\AlgoliaSearch\Model\AbstractModel;
use Algolia\AlgoliaSearch\Model\ModelInterface;

/**
 * Condition Class Doc Comment.
 *
 * @category Class
 */
class Condition extends AbstractModel implements ModelInterface, \ArrayAccess, \JsonSerializable
{
    /**
     * Array of property to type mappings. Used for (de)serialization.
     *
     * @var string[]
     */
    protected static $modelTypes = [
        'pattern' => 'string',
        'anchoring' => '\Algolia\AlgoliaSearch\Model\Search\Anchoring',
        'alternatives' => 'bool',
        'context' => 'string',
        'filters' => 'string',
    ];

    /**
     * Array of property to format mappings. Used for (de)serialization.
     *
     * @var string[]
     */
    protected static $modelFormats = [
        'pattern' => null,
        'anchoring' => null,
        'alternatives' => null,
        'context' => null,
        'filters' => null,
    ];

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name.
     *
     * @var string[]
     */
    protected static $attributeMap = [
        'pattern' => 'pattern',
        'anchoring' => 'anchoring',
        'alternatives' => 'alternatives',
        'context' => 'context',
        'filters' => 'filters',
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses).
     *
     * @var string[]
     */
    protected static $setters = [
        'pattern' => 'setPattern',
        'anchoring' => 'setAnchoring',
        'alternatives' => 'setAlternatives',
        'context' => 'setContext',
        'filters' => 'setFilters',
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests).
     *
     * @var string[]
     */
    protected static $getters = [
        'pattern' => 'getPattern',
        'anchoring' => 'getAnchoring',
        'alternatives' => 'getAlternatives',
        'context' => 'getContext',
        'filters' => 'getFilters',
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
        if (isset($data['pattern'])) {
            $this->container['pattern'] = $data['pattern'];
        }
        if (isset($data['anchoring'])) {
            $this->container['anchoring'] = $data['anchoring'];
        }
        if (isset($data['alternatives'])) {
            $this->container['alternatives'] = $data['alternatives'];
        }
        if (isset($data['context'])) {
            $this->container['context'] = $data['context'];
        }
        if (isset($data['filters'])) {
            $this->container['filters'] = $data['filters'];
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
     * Gets pattern.
     *
     * @return null|string
     */
    public function getPattern()
    {
        return $this->container['pattern'] ?? null;
    }

    /**
     * Sets pattern.
     *
     * @param null|string $pattern Query pattern that triggers the rule.  You can use either a literal string, or a special pattern `{facet:ATTRIBUTE}`, where `ATTRIBUTE` is a facet name. The rule is triggered if the query matches the literal string or a value of the specified facet. For example, with `pattern: {facet:genre}`, the rule is triggered when users search for a genre, such as \"comedy\".
     *
     * @return self
     */
    public function setPattern($pattern)
    {
        $this->container['pattern'] = $pattern;

        return $this;
    }

    /**
     * Gets anchoring.
     *
     * @return null|Anchoring
     */
    public function getAnchoring()
    {
        return $this->container['anchoring'] ?? null;
    }

    /**
     * Sets anchoring.
     *
     * @param null|Anchoring $anchoring anchoring
     *
     * @return self
     */
    public function setAnchoring($anchoring)
    {
        $this->container['anchoring'] = $anchoring;

        return $this;
    }

    /**
     * Gets alternatives.
     *
     * @return null|bool
     */
    public function getAlternatives()
    {
        return $this->container['alternatives'] ?? null;
    }

    /**
     * Sets alternatives.
     *
     * @param null|bool $alternatives whether the pattern should match plurals, synonyms, and typos
     *
     * @return self
     */
    public function setAlternatives($alternatives)
    {
        $this->container['alternatives'] = $alternatives;

        return $this;
    }

    /**
     * Gets context.
     *
     * @return null|string
     */
    public function getContext()
    {
        return $this->container['context'] ?? null;
    }

    /**
     * Sets context.
     *
     * @param null|string $context An additional restriction that only triggers the rule, when the search has the same value as `ruleContexts` parameter. For example, if `context: mobile`, the rule is only triggered when the search request has a matching `ruleContexts: mobile`. A rule context must only contain alphanumeric characters.
     *
     * @return self
     */
    public function setContext($context)
    {
        $this->container['context'] = $context;

        return $this;
    }

    /**
     * Gets filters.
     *
     * @return null|string
     */
    public function getFilters()
    {
        return $this->container['filters'] ?? null;
    }

    /**
     * Sets filters.
     *
     * @param null|string $filters Filters that trigger the rule.  You can add filters using the syntax `facet:value` so that the rule is triggered, when the specific filter is selected. You can use `filters` on its own or combine it with the `pattern` parameter. You can't combine multiple filters with `OR` and you can't use numeric filters.
     *
     * @return self
     */
    public function setFilters($filters)
    {
        $this->container['filters'] = $filters;

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
