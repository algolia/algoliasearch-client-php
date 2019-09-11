<?php

namespace Algolia\AlgoliaSearch\Compressors;

use Algolia\AlgoliaSearch\RequestOptions\RequestOptions;

/**
 * @internal
 */
interface Compressor
{
    /**
     * Mutates the given `$requestOptions` object and returns the encoded body.
     *
     * @param \Algolia\AlgoliaSearch\RequestOptions\RequestOptions $requestOptions
     * @param string                                               $body
     *
     * @return string
     */
    public function compress(RequestOptions $requestOptions, $body);
}
