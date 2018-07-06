<?php

namespace Algolia\AlgoliaSearch\Internals;

use Algolia\AlgoliaSearch\Config;
use Algolia\AlgoliaSearch\Helpers;

class RequestOptions
{
    private $headers = array();

    private $query = array();

    private $body = array();

    private $readTimeout;

    private $writeTimeout;

    private $connectTimeout;

    public function __construct(array $options = array())
    {
        foreach(array('headers', 'query', 'body') as $name) {
            if (isset($options[$name]) && !empty($options[$name])) {
                $this->{$name} = $options[$name];
            }
        }

        $this->readTimeout =
            isset($options['readTimeout']) ? $options['readTimeout'] : Config::getReadTimeout();
        $this->writeTimeout =
            isset($options['writeTimeout']) ? $options['writeTimeout'] : Config::getWriteTimeout();
        $this->connectTimeout =
            isset($options['connectTimeout']) ? $options['connectTimeout'] : Config::getConnectTimeout();
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function setHeaders($headers)
    {
        $this->headers = $headers;
        return $this;
    }

    public function addHeader($name, $value)
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function addHeaders($headers)
    {
        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }

    public function addDefaultHeader($name, $value)
    {
        if (!isset($this->headers[$name])) {
            $this->headers[$name] = $value;
        }
        return $this;
    }

    public function addDefaultHeaders($headers)
    {
        foreach ($headers as $name => $value) {
            $this->addDefaultHeader($name, $value);
        }
        return $this;
    }

    public function getQueryParameters()
    {
        return $this->query;
    }

    public function addQueryParameter($name, $value)
    {
        $this->query[$name] = $value;
        return $this;
    }

    public function addQueryParameters($headers)
    {
        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }

    public function setQueryParameters($queryParameters)
    {
        $this->query = $queryParameters;
        return $this;
    }

    public function getBuiltQueryParameters()
    {
        return Helpers::build_query($this->query);
    }

    public function getBody()
    {
        return $this->body;
    }

    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    public function getReadTimeout()
    {
        return $this->readTimeout;
    }

    public function setReadTimeout($readTimeout)
    {
        $this->readTimeout = $readTimeout;
        return $this;
    }

    public function getWriteTimeout()
    {
        return $this->writeTimeout;
    }

    public function setWriteTimeout($writeTimeout)
    {
        $this->writeTimeout = $writeTimeout;
        return $this;
    }

    public function getConnectTimeout()
    {
        return $this->connectTimeout;
    }

    public function setConnectTimeout($connectTimeout)
    {
        $this->connectTimeout = $connectTimeout;
        return $this;
    }
}
