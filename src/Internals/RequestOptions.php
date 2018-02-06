<?php

namespace Algolia\AlgoliaSearch\Internals;

use function GuzzleHttp\Psr7\build_query;

class RequestOptions
{
    private $options;

    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    public function getHeaders() : array
    {
        return $this->options['headers'];
    }

    public function getQuery() : array
    {
        return $this->options['query'];
    }

    public function getBuiltQuery() : string
    {
        return build_query($this->options['query']);
    }

    public function getBody() : array
    {
        return $this->options['body'];
    }

    public function getReadTimeout()
    {
        return $this->options['readTimeout'];
    }

    public function getWriteTimeout()
    {
        return $this->options['writeTimeout'];
    }

    public function getConnectTimeout()
    {
        return $this->options['connectTimeout'];
    }
}
