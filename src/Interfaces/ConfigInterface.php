<?php

namespace Algolia\AlgoliaSearch\Interfaces;

interface ConfigInterface
{
    public function getAppId();

    public function getApiKey();

    public function getHosts();

    public function getReadTimeout();

    public function getWriteTimeout();

    public function getConnectTimeout();
}
