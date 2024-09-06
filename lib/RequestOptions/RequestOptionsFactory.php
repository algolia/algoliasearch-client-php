<?php

namespace Algolia\AlgoliaSearch\RequestOptions;

use Algolia\AlgoliaSearch\Configuration\Configuration;
use Algolia\AlgoliaSearch\Support\AlgoliaAgent;

final class RequestOptionsFactory
{
    private $config;

    public function __construct(Configuration $config)
    {
        $this->config = $config;
    }

    /**
     * @param array|RequestOptions $options
     *
     * @return RequestOptions
     */
    public function create($options)
    {
        if (is_array($options)) {
            $options = $this->normalize($options);

            $options = new RequestOptions($options);
        } elseif ($options instanceof RequestOptions) {
            $options = $this->create($options);
        } else {
            throw new \InvalidArgumentException('RequestOptions can only be created from array or from RequestOptions object');
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
                'x-algolia-application-id' => $this->config->getAppId(),
                'x-algolia-api-key' => $this->config->getAlgoliaApiKey(),
                'User-Agent' => null !== $this->config->getAlgoliaAgent()
                        ? $this->config->getAlgoliaAgent()
                        : AlgoliaAgent::get($this->config->getClientName()),
                'Content-Type' => 'application/json',
            ],
            'queryParameters' => [],
            'body' => [],
            'readTimeout' => $this->config->getReadTimeout(),
            'writeTimeout' => $this->config->getWriteTimeout(),
            'connectTimeout' => $this->config->getConnectTimeout(),
        ];
        foreach ($options as $optionName => $value) {
            if (is_array($value) && 'headers' === $optionName) {
                $headersToLowerCase = [];
                foreach ($value as $key => $v) {
                    $headersToLowerCase[mb_strtolower($key)] = $v;
                }

                $normalized[$optionName] = array_merge(
                    $normalized[$optionName],
                    $headersToLowerCase
                );
            } else {
                $normalized[$optionName] = $value;
            }
        }

        return $normalized;
    }
}
