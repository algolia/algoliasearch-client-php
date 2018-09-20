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

    public function getDefaultForwardToReplicas();

    /**
     * Every methods accepting `forwardToReplicas` parameters will use
     * this value by default. If you don't set it, the engine default
     * value will be used.
     *
     * @param boolean $default
     * @return $this
     */
    public function setDefaultForwardToReplicas($default);


    /**
     * Gets the logger instance of the object.
     *
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger();
}
