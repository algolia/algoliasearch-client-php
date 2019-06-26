<?php

namespace Algolia\AlgoliaSearch\RetryStrategy;

interface ApiWrapperInterface
{
    /**
     * @param string $method
     * @param string $path
     * @param array  $requestOptions
     * @param array  $defaultRequestOptions
     *
     * @return array
     */
    public function read($method, $path, $requestOptions = array(), $defaultRequestOptions = array());

    /**
     * @param string $method
     * @param string $path
     * @param array  $data
     * @param array  $requestOptions
     * @param array  $defaultRequestOptions
     *
     * @return array
     */
    public function write($method, $path, $data = array(), $requestOptions = array(), $defaultRequestOptions = array());

    /**
     * @param string $method
     * @param string $path
     * @param array  $requestOptions
     * @param null   $hosts
     *
     * @return array
     */
    public function send($method, $path, $requestOptions = array(), $hosts = null);
}
