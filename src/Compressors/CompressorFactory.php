<?php

namespace Algolia\AlgoliaSearch\Compressors;

use Algolia\AlgoliaSearch\Config\AbstractConfig;
use Doctrine\Instantiator\Exception\InvalidArgumentException;

/**
 * @internal
 */
final class CompressorFactory
{
    /**
     * @param string $type
     *
     * @return \Algolia\AlgoliaSearch\Compressors\Compressor
     */
    public static function create($type)
    {
        switch ($type) {
            case AbstractConfig::COMPRESSION_TYPE_NONE:
                return new NullCompressor();
            case AbstractConfig::COMPRESSION_TYPE_GZIP:
                return new GzipCompressor();
            default:
                throw new InvalidArgumentException('Compression type not supported');
        }
    }
}
