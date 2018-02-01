<?php

namespace Algolia\AlgoliaSearch\Internals;

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

    public function getBody() : array
    {
        return $this->options['body'];
    }

    public function getTimeout()
    {
        return $this->options['timeout'];
    }

    public function getConnectTimeout()
    {
        return $this->options['connectTimeout'];
    }
}
