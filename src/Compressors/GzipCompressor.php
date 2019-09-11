<?php

namespace Algolia\AlgoliaSearch\Compressors;

use Algolia\AlgoliaSearch\RequestOptions\RequestOptions;

/**
 * @internal
 */
final class GzipCompressor implements Compressor
{
    public function compress(RequestOptions $requestOptions, $body)
    {
        $compressedBody = gzencode($body, 9);

        $requestOptions->addHeader('Content-Encoding', 'gzip');
        $requestOptions->addHeader('Content-Length', strlen($compressedBody));

        return $compressedBody;
    }
}
