<?php

namespace Algolia\AlgoliaSearch\Internals;

class RequestOptions
{
    private $options;

    public function __construct(array $options = array())
    {
        $this->options = $options;
    }

    public function getHeaders()
    {
        return $this->options['headers'];
    }

    public function getQuery()
    {
        return $this->options['query'];
    }

    public function getBuiltQuery()
    {
        return \GuzzleHttp\Psr7\build_query($this->options['query']);
    }

    public function getBody()
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
