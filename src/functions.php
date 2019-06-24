<?php

namespace Algolia\AlgoliaSearch;

/**
 * @param string      $pathFormat
 * @param string|null $args
 * @param string|null $_
 *
 * @return string
 */
function api_path($pathFormat, $args = null, $_ = null)
{
    return call_user_func_array(array('\Algolia\AlgoliaSearch\Support\Helpers', 'apiPath'), func_get_args());
}
