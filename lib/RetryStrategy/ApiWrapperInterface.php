<?php

namespace Algolia\AlgoliaSearch\RetryStrategy;

interface ApiWrapperInterface
{
    public function read($method, $path, $requestOptions = []);

    public function write($method, $path, $data = [], $requestOptions = []);

    public function send($method, $path, $requestOptions = [], $hosts = null);
}
