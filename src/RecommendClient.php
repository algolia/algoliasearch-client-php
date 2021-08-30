<?php

namespace Algolia\AlgoliaSearch;

use Algolia\AlgoliaSearch\Config\RecommendConfig;
use Algolia\AlgoliaSearch\Exceptions\AlgoliaException;
use Algolia\AlgoliaSearch\RequestOptions\RequestOptions;
use Algolia\AlgoliaSearch\RetryStrategy\ApiWrapper;
use Algolia\AlgoliaSearch\RetryStrategy\ClusterHosts;

final class RecommendClient
{
    const RELATED_PRODUCTS = 'related-products';
    const BOUGHT_TOGETHER = 'bought-together';

    /**
     * @var ApiWrapper
     */
    private $api;

    /**
     * @var RecommendConfig
     */
    private $config;

    public function __construct(ApiWrapper $api, RecommendConfig $config)
    {
        $this->api = $api;
        $this->config = $config;
    }

    public static function create($appId = null, $apiKey = null, $region = null)
    {
        $config = RecommendConfig::create($appId, $apiKey, $region);

        return static::createWithConfig($config);
    }

    public static function createWithConfig(RecommendConfig $config)
    {
        $config = clone $config;

        if ($hosts = $config->getHosts()) {
            // If a list of hosts was passed, we ignore the cache
            $clusterHosts = ClusterHosts::create($hosts);
        } else {
            $clusterHosts = ClusterHosts::createFromAppId($config->getAppId());
        }

        $apiWrapper = new ApiWrapper(
            Algolia::getHttpClient(),
            $config,
            $clusterHosts
        );

        return new static($apiWrapper, $config);
    }

    /**
     * Get recommendations.
     *
     * @param array|RequestOptions $requestOptions
     *
     * @return array
     */
    public function getRecommendations(array $queries, $requestOptions = [])
    {
        foreach ($queries as $key => $query) {
            // The `threshold` param is required by the endpoint to make it easier to provide a default value later,
            // so we default it in the client so that users don't have to provide a value.
            if (!isset($query['threshold'])) {
                $queries[$key]['threshold'] = 0;
            }
            // Unset fallbackParameters if the model is 'bought-together'
            if (self::BOUGHT_TOGETHER === $query['model'] && isset($query['fallbackParameters'])) {
                unset($queries[$key]['fallbackParameters']);
            }
        }

        $requests = [
            'requests' => $queries,
        ];

        return $this->api->write(
            'POST',
            api_path('/1/indexes/*/recommendations'),
            $requests,
            $requestOptions
        );
    }

    /**
     * Get Related products.
     *
     * @param array|RequestOptions $requestOptions
     *
     * @return array
     *
     * @throws AlgoliaException
     */
    public function getRelatedProducts(array $queries, $requestOptions = [])
    {
        $queries = $this->setModel($queries, self::RELATED_PRODUCTS);

        return $this->getRecommendations($queries, $requestOptions);
    }

    /**
     * Get product frequently bought together.
     *
     * @param array|RequestOptions $requestOptions
     *
     * @return array
     *
     * @throws AlgoliaException
     */
    public function getFrequentlyBoughtTogether(array $queries, $requestOptions = [])
    {
        $queries = $this->setModel($queries, self::BOUGHT_TOGETHER);

        return $this->getRecommendations($queries, $requestOptions);
    }

    /**
     * Add the model for related products and product frequently bought together.
     *
     * @param string $model can be either 'related-products' or 'bought-together'
     *
     * @return array
     *
     * @throws AlgoliaException
     */
    private function setModel(array $queries, $model)
    {
        foreach ($queries as $key => $query) {
            $queries[$key]['model'] = $model;
        }

        return $queries;
    }
}
