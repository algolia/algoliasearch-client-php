<?php

namespace Algolia\AlgoliaSearch\Interfaces;

use Psr\Log\LoggerAwareInterface;

interface ClientConfigInterface extends LoggerAwareInterface
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

    /**
     * Gets the logger instance of the object.
     *
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger();
}
