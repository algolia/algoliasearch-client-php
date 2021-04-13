<?php

namespace Algolia\AlgoliaSearch;

function api_path($pathFormat, $args = null, $_ = null)
{
    return call_user_func_array(['\Algolia\AlgoliaSearch\Support\Helpers', 'apiPath'], func_get_args());
}
