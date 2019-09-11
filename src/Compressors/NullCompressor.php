<?php

namespace Algolia\AlgoliaSearch\Compressors;

use Algolia\AlgoliaSearch\RequestOptions\RequestOptions;

/**
 * @internal
 */
final class NullCompressor implements Compressor
{
    public function compress(RequestOptions $requestOptions, $body)
    {
        return $body;
    }
}
