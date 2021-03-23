<?php

namespace Algolia\AlgoliaSearch\Support;

use Algolia\AlgoliaSearch\Exceptions\MissingObjectId;

final class Helpers
{
    /**
     * Use this function to generate API path. It will ensure
     * that all parameters are properly urlencoded.
     * The function will not double encode if the params are
     * already urlencoded
     * Signature is the same `sprintf`.
     *
     * Examples:
     *      - api_path('1/indexes/%s/search', $indexName)
     *      - api_path('/1/indexes/%s/synonyms/%s', $indexName, $objectID)
     *
     * @param string $pathFormat
     * @param mixed  $args
     * @param mixed  $_
     *
     * @return mixed
     */
    public static function apiPath($pathFormat, $args = null, $_ = null)
    {
        $arguments = array_slice(func_get_args(), 1);
        foreach ($arguments as &$arg) {
            $arg = urlencode(urldecode($arg));
        }
        array_unshift($arguments, $pathFormat);

        return call_user_func_array('sprintf', $arguments);
    }

    /**
     * When building a query string, array values must be json_encoded.
     * This function can be used to turn any array into a Algolia-valid query string.
     *
     * Do not use a typical implementation where ['key' => ['one', 'two']] is
     * turned into key[1]=one&key[2]=two. Algolia will not understand key[x].
     * It should be turned into key=['one','two'] (before being url_encoded).
     *
     * @return string The urlencoded query string to send to Algolia
     */
    public static function buildQuery(array $args)
    {
        if (!$args) {
            return '';
        }

        $args = array_map(function ($value) {
            if (is_array($value)) {
                return json_encode($value);
            } elseif (is_bool($value)) {
                return $value ? 'true' : 'false';
            } else {
                return $value;
            }
        }, $args);

        return http_build_query($args);
    }

    public static function buildBatch($items, $action)
    {
        return array_map(function ($item) use ($action) {
            return [
                'action' => $action,
                'body' => $item,
            ];
        }, $items);
    }

    public static function ensureObjectID($objects, $message = 'ObjectID is required to add a record, a synonym or a query rule.')
    {
        // In case a single objects is passed
        if (isset($objects['objectID'])) {
            return;
        }

        // In case multiple objects are passed
        foreach ($objects as $object) {
            if (!isset($object['objectID']) && !isset($object['body']['objectID'])) {
                throw new MissingObjectId($message);
            }
        }
    }

    /**
     * Wrapper for json_decode that throws when an error occurs.
     *
     * This function is extracted from Guzzlehttp/Guzzle package which is not
     * compatible with PHP 5.3 so the client cannot always use it.
     *
     * @param string $json  JSON data to parse
     * @param bool   $assoc when true, returned objects will be converted
     *                      into associative arrays
     * @param int    $depth user specified recursion depth
     *
     * @return mixed
     *
     * @throws \InvalidArgumentException if the JSON cannot be decoded
     *
     * @see http://www.php.net/manual/en/function.json-decode.php
     */
    public static function json_decode($json, $assoc = false, $depth = 512)
    {
        $data = \json_decode($json, $assoc, $depth);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \InvalidArgumentException('json_decode error: '.json_last_error_msg());
        }

        return $data;
    }

    public static function mapObjectIDs($objectIDKey, $objects)
    {
        return array_map(function ($object) use ($objectIDKey) {
            if (!isset($object[$objectIDKey])) {
                throw new MissingObjectId("At least one object is missing the required $objectIDKey key: ".json_encode($object));
            }
            $object['objectID'] = $object[$objectIDKey];

            return $object;
        }, $objects);
    }

    public static function serializeQueryParameters($parameters)
    {
        if (is_string($parameters)) {
            return $parameters;
        }

        foreach ($parameters as $key => $value) {
            if (is_array($value)) {
                $parameters[$key] = json_encode($value, JSON_THROW_ON_ERROR);
            }
        }

        return http_build_query($parameters);
    }
}
