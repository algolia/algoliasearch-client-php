<?php

namespace Algolia\AlgoliaSearch;

use Algolia\AlgoliaSearch\Support\Helpers;

function api_path($pathFormat, $args = null, $_ = null)
{
    return call_user_func_array([Helpers::class, 'apiPath'], func_get_args());
}
