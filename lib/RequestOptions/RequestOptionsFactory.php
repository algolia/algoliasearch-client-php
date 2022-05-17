<?php

namespace Algolia\AlgoliaSearch\RequestOptions;

use Algolia\AlgoliaSearch\Configuration\Configuration;
use Algolia\AlgoliaSearch\Support\UserAgent;

final class RequestOptionsFactory
{
    private $config;

    public function __construct(Configuration $config)
    {
        $this->config = $config;
    }

    /**
     * @param \Algolia\AlgoliaSearch\RequestOptions\RequestOptions|array $options
     * @param array                                                      $defaults
     *
     * @return \Algolia\AlgoliaSearch\RequestOptions\RequestOptions
     */
    public function create($options)
    {
        if (is_array($options)) {
            $options = $this->normalize($options);

            $options = new RequestOptions($options);
        } elseif ($options instanceof RequestOptions) {
            $options = $this->create($options);
        } else {
            throw new \InvalidArgumentException(
                'RequestOptions can only be created from array or from RequestOptions object'
            );
        }

        return $options->addDefaultHeaders($this->config->getDefaultHeaders());
    }

    public function createBodyLess($options)
    {
        $options = $this->create($options);

        return $options->addQueryParameters($options->getBody())->setBody([]);
    }

    private function normalize($options)
    {
        $normalized = [
            'headers' => [
                'X-Algolia-Application-Id' => $this->config->getAppId(),
                'X-Algolia-API-Key' => $this->config->getAlgoliaApiKey(),
                'User-Agent' => $this->config->getUserAgent() !== null
                        ? $this->config->getUserAgent()
                        : UserAgent::get(),
                'Content-Type' => 'application/json',
            ],
            'queryParameters' => [],
            'body' => [],
            'readTimeout' => $this->config->getReadTimeout(),
            'writeTimeout' => $this->config->getWriteTimeout(),
            'connectTimeout' => $this->config->getConnectTimeout(),
        ];

        foreach ($options as $optionName => $value) {
            if (is_array($value)) {
                if ($optionName === 'headers') {
                    $headersToLowerCase = [];

                    foreach ($value as $key => $v) {
                        $headersToLowerCase[mb_strtolower($key)] = $v;
                    }

                    $normalized[$optionName] = $this->format(
                        $headersToLowerCase
                    );
                } else {
                    $normalized[$optionName] = $this->format(
                        $value,
                        $optionName === 'queryParameters'
                    );
                }
            } else {
                $normalized[$optionName] = $value;
            }
        }

        return $normalized;
    }

    private function format($options, $isQueryParameters = false)
    {
        foreach ($options as $name => $value) {
            if (is_array($value) && $isQueryParameters) {
                $options[$name] = implode(',', $value);
            }
        }

        return $options;
    }
}
