<?php

namespace Algolia\AlgoliaSearch\Interfaces;

interface ClientConfigInterface
{
    public static function create($appId = null, $apiKey = null);

    public function getDefaultConfig();

    public function getAppId();

    public function getApiKey();

    public function getHosts();

    public function getReadTimeout();

    public function getWriteTimeout();

    public function getConnectTimeout();

    public function getWaitTaskMaxRetry();

    public function getWaitTaskTimeBeforeRetry();

    public function getBatchSize();
}
