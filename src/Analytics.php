<?php

namespace Algolia\AlgoliaSearch;

use Algolia\AlgoliaSearch\Exceptions\AlgoliaException;
use Algolia\AlgoliaSearch\Interfaces\ClientConfigInterface;
use Algolia\AlgoliaSearch\RetryStrategy\ApiWrapper;
use Algolia\AlgoliaSearch\RetryStrategy\ClusterHosts;
use Algolia\AlgoliaSearch\Support\ClientConfig;
use Algolia\AlgoliaSearch\Support\HttpLayer;

final class Analytics
{
    /**
     * @var ApiWrapper
     */
    private $api;

    /**
     * @var ClientConfigInterface
     */
    private $config;

    public function __construct(ApiWrapper $api, ClientConfigInterface $config)
    {
        $this->api = $api;
        $this->config = $config;
    }

    public static function create($appId = null, $apiKey = null)
    {
        $config = ClientConfig::create($appId, $apiKey);
        $config->setHosts(ClusterHosts::createForAnalytics());

        return static::createWithConfig($config);
    }

    public static function createWithConfig(ClientConfigInterface $config)
    {
        $apiWrapper = new ApiWrapper(
            HttpLayer::get(),
            $config
        );

        return new static($apiWrapper, $config);
    }

    public function getABTests($params = array())
    {
        $params += array('offset' => 0, 'limit' => 10);

        return $this->api->read('GET', api_path('/2/abtests'), $params);
    }

    public function getABTest($abTestID)
    {
        if (!$abTestID) {
            throw new AlgoliaException('Cannot retrieve ABTest because the abtestID is invalid.');
        }

        return $this->api->read('GET', api_path('/2/abtests/%s', $abTestID));
    }

    public function addABTest($abTest)
    {
        return $this->api->write('POST', api_path('/2/abtests'), array(), $abTest);
    }

    public function stopABTest($abTestID)
    {
        if (!$abTestID) {
            throw new AlgoliaException('Cannot retrieve ABTest because the abtestID is invalid.');
        }

        return $this->api->write('POST', api_path('/2/abtests/%s', $abTestID));
    }

    public function deleteABTest($abTestID)
    {
        if (!$abTestID) {
            throw new AlgoliaException('Cannot retrieve ABTest because the abtestID is invalid.');
        }

        return $this->api->write('DELETE', api_path('/2/abtests/%s', $abTestID));
    }

    public function custom($method, $path, $requestOptions = array(), $hosts = null)
    {
        return $this->api->send($method, $path, $requestOptions, $hosts);
    }
}
