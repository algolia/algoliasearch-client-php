<?php

namespace Algolia\AlgoliaSearch;

use Algolia\AlgoliaSearch\Config\AnalyticsConfig;
use Algolia\AlgoliaSearch\Exceptions\AlgoliaException;
use Algolia\AlgoliaSearch\Http\HttpClientFactory;
use Algolia\AlgoliaSearch\RetryStrategy\ApiWrapper;
use Algolia\AlgoliaSearch\RetryStrategy\ClusterHosts;

final class Analytics
{
    /**
     * @var ApiWrapper
     */
    private $api;

    /**
     * @var AnalyticsConfig
     */
    private $config;

    public function __construct(ApiWrapper $api, AnalyticsConfig $config)
    {
        $this->api = $api;
        $this->config = $config;
    }

    public static function create($appId = null, $apiKey = null)
    {
        return static::createWithConfig(AnalyticsConfig::create($appId, $apiKey));
    }

    public static function createWithConfig(AnalyticsConfig $config)
    {
        $config = clone $config;

        if ($hosts = $config->getHosts()) {
            $clusterHosts = ClusterHosts::create($hosts);
        } else {
            $clusterHosts = ClusterHosts::createForAnalytics();
        }

        $apiWrapper = new ApiWrapper(
            HttpClientFactory::get(),
            $config,
            $clusterHosts
        );

        return new static($apiWrapper, $config);
    }

    /**
     * Get a paginated list of AB Test from the API.
     *
     * @param array $params
     *
     * @return array
     */
    public function getABTests($params = array())
    {
        $params += array('offset' => 0, 'limit' => 10);

        return $this->api->read('GET', api_path('/2/abtests'), $params);
    }

    /**
     * Get an AB Test.
     *
     * @param int $abTestID Id of the AB Test to retrieve
     *
     * @return array
     *
     * @throws \Algolia\AlgoliaSearch\Exceptions\AlgoliaException
     */
    public function getABTest($abTestID)
    {
        if (!$abTestID) {
            throw new AlgoliaException('Cannot retrieve ABTest because the abtestID is invalid.');
        }

        return $this->api->read('GET', api_path('/2/abtests/%s', $abTestID));
    }

    /**
     * Create new AB Test.
     *
     * @param array $abTest
     *
     * @return array Information about the creation like TaskID and date
     */
    public function addABTest($abTest)
    {
        return $this->api->write('POST', api_path('/2/abtests'), array(), $abTest);
    }

    /**
     * Stop a running AB Test.
     *
     * @param int $abTestID
     *
     * @return array
     *
     * @throws \Algolia\AlgoliaSearch\Exceptions\AlgoliaException
     */
    public function stopABTest($abTestID)
    {
        if (!$abTestID) {
            throw new AlgoliaException('Cannot retrieve ABTest because the abtestID is invalid.');
        }

        return $this->api->write('POST', api_path('/2/abtests/%s', $abTestID));
    }

    /**
     * Delete an AB Test.
     *
     * @param int $abTestID
     *
     * @return array
     *
     * @throws \Algolia\AlgoliaSearch\Exceptions\AlgoliaException
     */
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
