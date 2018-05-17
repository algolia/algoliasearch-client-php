<?php

namespace Algolia\AlgoliaSearch;

if (! function_exists('api_path')) {
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
     * @return mixed
     */
    function api_path($pathFormat, $args = null, $_ = null) {
        $arguments = array_slice(func_get_args(), 1);
        foreach ($arguments as &$arg) {
            $arg = urlencode(urldecode($arg));
        }
        array_unshift($arguments, $pathFormat);

        return call_user_func_array('sprintf', $arguments);
    }
}
