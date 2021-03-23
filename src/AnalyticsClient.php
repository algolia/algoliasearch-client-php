<?php

namespace Algolia\AlgoliaSearch;

use Algolia\AlgoliaSearch\Config\AnalyticsConfig;
use Algolia\AlgoliaSearch\Exceptions\AlgoliaException;
use Algolia\AlgoliaSearch\RetryStrategy\ApiWrapper;
use Algolia\AlgoliaSearch\RetryStrategy\ApiWrapperInterface;
use Algolia\AlgoliaSearch\RetryStrategy\ClusterHosts;

final class AnalyticsClient
{
    /**
     * @var ApiWrapperInterface
     */
    private $api;

    /**
     * @var AnalyticsConfig
     */
    private $config;

    public function __construct(ApiWrapperInterface $api, AnalyticsConfig $config)
    {
        $this->api = $api;
        $this->config = $config;
    }

    public static function create($appId = null, $apiKey = null, $region = null)
    {
        return static::createWithConfig(AnalyticsConfig::create($appId, $apiKey, $region));
    }

    public static function createWithConfig(AnalyticsConfig $config)
    {
        $config = clone $config;

        if ($hosts = $config->getHosts()) {
            $clusterHosts = ClusterHosts::create($hosts);
        } else {
            $clusterHosts = ClusterHosts::createForAnalytics($config->getRegion());
        }

        $apiWrapper = new ApiWrapper(
            Algolia::getHttpClient(),
            $config,
            $clusterHosts
        );

        return new static($apiWrapper, $config);
    }

    /**
     * Get a paginated list of AB Test from the API.
     *
     * @param array|\Algolia\AlgoliaSearch\RequestOptions\RequestOptions $requestOptions
     *
     * @return array
     */
    public function getABTests($requestOptions = [])
    {
        return $this->api->read('GET', api_path('/2/abtests'), $requestOptions);
    }

    /**
     * Get an AB Test.
     *
     * @param int                                                        $abTestID       Id of the AB Test to retrieve
     * @param array|\Algolia\AlgoliaSearch\RequestOptions\RequestOptions $requestOptions
     *
     * @return array
     *
     * @throws \Algolia\AlgoliaSearch\Exceptions\AlgoliaException
     */
    public function getABTest($abTestID, $requestOptions = [])
    {
        if (!$abTestID) {
            throw new AlgoliaException('Cannot retrieve ABTest because the abtestID is invalid.');
        }

        return $this->api->read('GET', api_path('/2/abtests/%s', $abTestID));
    }

    /**
     * Create new AB Test.
     *
     * @param array                                                      $abTest
     * @param array|\Algolia\AlgoliaSearch\RequestOptions\RequestOptions $requestOptions
     *
     * @return array Information about the creation like TaskID and date
     */
    public function addABTest($abTest, $requestOptions = [])
    {
        return $this->api->write('POST', api_path('/2/abtests'), $abTest, $requestOptions);
    }

    /**
     * Stop a running AB Test.
     *
     * @param int                                                        $abTestID
     * @param array|\Algolia\AlgoliaSearch\RequestOptions\RequestOptions $requestOptions
     *
     * @return array
     *
     * @throws \Algolia\AlgoliaSearch\Exceptions\AlgoliaException
     */
    public function stopABTest($abTestID, $requestOptions = [])
    {
        if (!$abTestID) {
            throw new AlgoliaException('Cannot retrieve ABTest because the abtestID is invalid.');
        }

        return $this->api->write('POST', api_path('/2/abtests/%s/stop', $abTestID), [], $requestOptions);
    }

    /**
     * Delete an AB Test.
     *
     * @param int                                                        $abTestID
     * @param array|\Algolia\AlgoliaSearch\RequestOptions\RequestOptions $requestOptions
     *
     * @return array
     *
     * @throws \Algolia\AlgoliaSearch\Exceptions\AlgoliaException
     */
    public function deleteABTest($abTestID, $requestOptions = [])
    {
        if (!$abTestID) {
            throw new AlgoliaException('Cannot retrieve ABTest because the abtestID is invalid.');
        }

        return $this->api->write('DELETE', api_path('/2/abtests/%s', $abTestID), [], $requestOptions);
    }

    public function custom($method, $path, $requestOptions = [], $hosts = null)
    {
        return $this->api->send($method, $path, $requestOptions, $hosts);
    }
}
