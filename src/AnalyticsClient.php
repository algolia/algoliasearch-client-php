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
     * @param \Algolia\AlgoliaSearch\RequestOptions\RequestOptions|array $requestOptions
     *
     * @return array
     */
    public function getABTests($requestOptions = array())
    {
        return $this->api->read('GET', api_path('/2/abtests'), $requestOptions);
    }

    /**
     * Get an AB Test.
     *
     * @param int                                                        $abTestID       Id of the AB Test to retrieve
     * @param \Algolia\AlgoliaSearch\RequestOptions\RequestOptions|array $requestOptions
     *
     * @throws \Algolia\AlgoliaSearch\Exceptions\AlgoliaException
     *
     * @return array
     */
    public function getABTest($abTestID, $requestOptions = array())
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
     * @param \Algolia\AlgoliaSearch\RequestOptions\RequestOptions|array $requestOptions
     *
     * @return array Information about the creation like TaskID and date
     */
    public function addABTest($abTest, $requestOptions = array())
    {
        return $this->api->write('POST', api_path('/2/abtests'), $abTest, $requestOptions);
    }

    /**
     * Stop a running AB Test.
     *
     * @param int                                                        $abTestID
     * @param \Algolia\AlgoliaSearch\RequestOptions\RequestOptions|array $requestOptions
     *
     * @throws \Algolia\AlgoliaSearch\Exceptions\AlgoliaException
     *
     * @return array
     */
    public function stopABTest($abTestID, $requestOptions = array())
    {
        if (!$abTestID) {
            throw new AlgoliaException('Cannot retrieve ABTest because the abtestID is invalid.');
        }

        return $this->api->write('POST', api_path('/2/abtests/%s', $abTestID), array(), $requestOptions);
    }

    /**
     * Delete an AB Test.
     *
     * @param int                                                        $abTestID
     * @param \Algolia\AlgoliaSearch\RequestOptions\RequestOptions|array $requestOptions
     *
     * @throws \Algolia\AlgoliaSearch\Exceptions\AlgoliaException
     *
     * @return array
     */
    public function deleteABTest($abTestID, $requestOptions = array())
    {
        if (!$abTestID) {
            throw new AlgoliaException('Cannot retrieve ABTest because the abtestID is invalid.');
        }

        return $this->api->write('DELETE', api_path('/2/abtests/%s', $abTestID), array(), $requestOptions);
    }

    public function custom($method, $path, $requestOptions = array(), $hosts = null)
    {
        return $this->api->send($method, $path, $requestOptions, $hosts);
    }
}
