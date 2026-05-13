<?php

namespace Algolia\AlgoliaSearch\Configuration;

use Algolia\AlgoliaSearch\Exceptions\AlgoliaException;

/**
 * Configuration options for the ingestion transporter used by the `*WithTransformation` helpers
 * on the `SearchClient`.
 *
 * When passed to `SearchClient::createWithConfig` (via `SearchConfig::setTransformationOptions`)
 * or to `SearchClient::setTransformationOptions`, an ingestion transporter is eagerly created
 * using the Ingestion API defaults (25s connect/read/write timeouts, region-derived hosts).
 * Only the fields explicitly set via the fluent setters override those defaults — the parent
 * `SearchConfig` is never forwarded to the ingestion transporter.
 *
 * Setter names mirror `IngestionConfig` / the base `Configuration` 1:1, so call sites look
 * identical to other PHP config code.
 *
 * @see https://www.algolia.com/doc/libraries/sdk/methods/ingestion/
 */
final class TransformationOptions
{
    /**
     * @var string Algolia region for the Ingestion API (`"us"` or `"eu"`). Required.
     */
    private $region;

    /**
     * @var null|array
     */
    private $hosts;

    /**
     * @var bool
     */
    private $hasFullHosts = false;

    /**
     * @var null|int
     */
    private $readTimeout;

    /**
     * @var null|int
     */
    private $writeTimeout;

    /**
     * @var null|int
     */
    private $connectTimeout;

    /**
     * @var null|int
     */
    private $waitTaskTimeBeforeRetry;

    /**
     * @var null|array<string,string>
     */
    private $defaultHeaders;

    /**
     * @var null|string
     */
    private $compressionType;

    /**
     * @param string $region Algolia region for the Ingestion API (`"us"` or `"eu"`). Required.
     *
     * @throws AlgoliaException if `$region` is missing or empty
     */
    public function __construct($region)
    {
        if (null === $region || '' === $region) {
            throw new AlgoliaException(
                '`region` is required in `transformationOptions`. See https://www.algolia.com/doc/libraries/sdk/methods/ingestion/'
            );
        }

        $this->region = $region;
    }

    /**
     * @return string
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * Sets the ingestion-transporter hosts using short hostnames (the transporter prepends the app id).
     *
     * @param array $hosts
     *
     * @return $this
     */
    public function setHosts($hosts)
    {
        $this->hosts = $hosts;
        $this->hasFullHosts = false;

        return $this;
    }

    /**
     * Sets the ingestion-transporter hosts using full URLs (no rewriting).
     *
     * @param array $hosts
     *
     * @return $this
     */
    public function setFullHosts($hosts)
    {
        $this->hosts = $hosts;
        $this->hasFullHosts = true;

        return $this;
    }

    /**
     * @return null|array
     */
    public function getHosts()
    {
        return $this->hosts;
    }

    /**
     * @return bool
     */
    public function getHasFullHosts()
    {
        return $this->hasFullHosts;
    }

    /**
     * @param int $readTimeout
     *
     * @return $this
     */
    public function setReadTimeout($readTimeout)
    {
        $this->readTimeout = $readTimeout;

        return $this;
    }

    /**
     * @return null|int
     */
    public function getReadTimeout()
    {
        return $this->readTimeout;
    }

    /**
     * @param int $writeTimeout
     *
     * @return $this
     */
    public function setWriteTimeout($writeTimeout)
    {
        $this->writeTimeout = $writeTimeout;

        return $this;
    }

    /**
     * @return null|int
     */
    public function getWriteTimeout()
    {
        return $this->writeTimeout;
    }

    /**
     * @param int $connectTimeout
     *
     * @return $this
     */
    public function setConnectTimeout($connectTimeout)
    {
        $this->connectTimeout = $connectTimeout;

        return $this;
    }

    /**
     * @return null|int
     */
    public function getConnectTimeout()
    {
        return $this->connectTimeout;
    }

    /**
     * @param int $waitTaskTimeBeforeRetry
     *
     * @return $this
     */
    public function setWaitTaskTimeBeforeRetry($waitTaskTimeBeforeRetry)
    {
        $this->waitTaskTimeBeforeRetry = $waitTaskTimeBeforeRetry;

        return $this;
    }

    /**
     * @return null|int
     */
    public function getWaitTaskTimeBeforeRetry()
    {
        return $this->waitTaskTimeBeforeRetry;
    }

    /**
     * @param array<string,string> $defaultHeaders
     *
     * @return $this
     */
    public function setDefaultHeaders(array $defaultHeaders)
    {
        $this->defaultHeaders = $defaultHeaders;

        return $this;
    }

    /**
     * @return null|array<string,string>
     */
    public function getDefaultHeaders()
    {
        return $this->defaultHeaders;
    }

    /**
     * @param string $compressionType
     *
     * @return $this
     */
    public function setCompressionType($compressionType)
    {
        $this->compressionType = $compressionType;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getCompressionType()
    {
        return $this->compressionType;
    }
}
