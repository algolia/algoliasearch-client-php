<?php

declare(strict_types=1);

namespace Algolia\AlgoliaSearch\RetryStrategy;

interface ApiWrapperInterface
{
    public function read($method, $path, $requestOptions = array(), $defaultRequestOptions = array());

    public function write($method, $path, $data = array(), $requestOptions = array(), $defaultRequestOptions = array());

    public function send($method, $path, $requestOptions = array(), $hosts = null);
}
