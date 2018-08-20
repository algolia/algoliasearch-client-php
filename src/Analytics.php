<?php

namespace Algolia\AlgoliaSearch;

use Algolia\AlgoliaSearch\Exceptions\AlgoliaException;
use Algolia\AlgoliaSearch\Internals\ApiWrapper;
use Algolia\AlgoliaSearch\Internals\ClusterHosts;
use Algolia\AlgoliaSearch\Support\ClientConfig;
use Algolia\AlgoliaSearch\Support\Config;

final class Analytics
{
    /**
     * @var ApiWrapper
     */
    private $api;

    public function __construct(ApiWrapper $api)
    {
        $this->api = $api;
    }

    public static function create($appId = null, $apiKey = null)
    {
        $config = new ClientConfig($appId, $apiKey);
        $config->setHosts(ClusterHosts::createForAnalytics());

        return static::createWithConfig($config);
    }

    public static function createWithConfig(ClientConfig $config)
    {
        $apiWrapper = new ApiWrapper(
            Config::getHttpClient(),
            $config
        );

        return new static($apiWrapper);
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
