<?php

namespace Algolia\AlgoliaSearch;

use Algolia\AlgoliaSearch\Exceptions\AlgoliaException;
use Algolia\AlgoliaSearch\Http\HttpClientFactory;
use Algolia\AlgoliaSearch\Interfaces\ClientConfigInterface;
use Algolia\AlgoliaSearch\RetryStrategy\ApiWrapper;
use Algolia\AlgoliaSearch\RetryStrategy\ClusterHosts;
use Algolia\AlgoliaSearch\Config\ClientConfig;

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
        $config = clone $config;

        if ($hosts = $config->getHosts()) {
            $clusterHosts = ClusterHosts::create($hosts);
        } else {
            $clusterHosts = ClusterHosts::createForAnalytics();
        }

        $apiWrapper = new ApiWrapper(
            HttpClientFactory::get($config),
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
