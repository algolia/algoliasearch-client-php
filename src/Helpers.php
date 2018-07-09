<?php

namespace Algolia\AlgoliaSearch;

use Algolia\AlgoliaSearch\Exceptions\MissingObjectId;

class Helpers
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
     * @param      $pathFormat
     * @param null $args
     * @param null $_
     *
     * @return mixed
     */
    public static function api_path($pathFormat, $args = null, $_ = null    )
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
     * This function can be used to turn any array into a Algilia-valid query string.
     *
     * Do not use a typical implementation where ['key' => ['one', 'two']] is
     * turned into key[1]=one&key[2]=two. Algolia will not understand key[x].
     * It should be turned into key=['one','two'] (before being url_encoded).
     *
     * @param array $args
     *
     * @return string The urlencoded query string to send to Algolia
     */
    public static function build_query(array $args)
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

    public static function build_batch($items, $action)
    {
        return array_map(function ($item) use ($action) {
            return array(
                'action' => $action,
                'body' => $item,
            );
        }, $items);
    }

    public static function ensure_objectID($objects, $message = 'ObjectID is required to add a record, a synonym or a query rule.')
    {
        // In case a single objects is passed
        if (isset($objects['objectID'])) {
            return;
        }

        // In case multiple objects are passed
        foreach ($objects as $o) {
            if (!isset($o['objectID'])) {
                throw new MissingObjectId($message);
            }
        }
    }
}
