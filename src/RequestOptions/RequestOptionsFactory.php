<?php

namespace Algolia\AlgoliaSearch\RequestOptions;

use Algolia\AlgoliaSearch\Config\AbstractConfig;
use Algolia\AlgoliaSearch\Support\UserAgent;

final class RequestOptionsFactory
{
    /**
     * @var AbstractConfig
     */
    private $config;

    /**
     * @var array
     */
    private $validQueryParameters = array(
        'forwardToReplicas',
        'replaceExistingSynonyms',
        'clearExistingRules',
        'getVersion',
    );

    /**
     * @var array
     */
    private $validHeaders = array(
        'Content-type',
        'User-Agent',
        'createIfNotExists',
    );

    /**
     * RequestOptionsFactory constructor.
     *
     * @param AbstractConfig $config
     */
    public function __construct(AbstractConfig $config)
    {
        $this->config = $config;
    }

    /**
     * @param \Algolia\AlgoliaSearch\RequestOptions\RequestOptions|array $options
     * @param array                                                      $defaults
     *
     * @return \Algolia\AlgoliaSearch\RequestOptions\RequestOptions
     */
    public function create($options, $defaults = array())
    {
        if (is_array($options)) {
            $options += $defaults;
            $options = $this->format($options);
            $options = $this->normalize($options);

            $options = new RequestOptions($options);
        } elseif ($options instanceof RequestOptions) {
            $defaults = $this->create($defaults);
            $options->addDefaultHeaders($defaults->getHeaders());
            $options->addDefaultQueryParameters($defaults->getQueryParameters());
            $options->addDefaultBodyParameters($defaults->getBody());
        } else {
            throw new \InvalidArgumentException(
                'RequestOptions can only be created from array or from RequestOptions object'
            );
        }

        return $options->addDefaultHeaders($this->config->getDefaultHeaders());
    }

    /**
     * @param array $options
     * @param array $defaults
     *
     * @return RequestOptions
     */
    public function createBodyLess($options, $defaults = array())
    {
        $options = $this->create($options, $defaults);

        return $options
            ->addQueryParameters($options->getBody())
            ->setBody(array());
    }

    /**
     * @param array $options
     *
     * @return array
     */
    private function normalize($options)
    {
        $normalized = array(
            'headers' => array(
                'X-Algolia-Application-Id' => $this->config->getAppId(),
                'X-Algolia-API-Key' => $this->config->getApiKey(),
                'User-Agent' => UserAgent::get(),
                'Content-Type' => 'application/json',
            ),
            'query' => array(),
            'body' => array(),
            'readTimeout' => $this->config->getReadTimeout(),
            'writeTimeout' => $this->config->getWriteTimeout(),
            'connectTimeout' => $this->config->getConnectTimeout(),
        );

        foreach ($options as $optionName => $value) {
            $type = $this->getOptionType($optionName);

            if (in_array($type, array('readTimeout', 'writeTimeout', 'connectTimeout'), true)) {
                $normalized[$type] = $value;
            } else {
                $normalized[$type][$optionName] = $value;
            }
        }

        return $normalized;
    }

    /**
     * @param array $options
     *
     * @return mixed
     */
    private function format($options)
    {
        foreach ($options as $name => $value) {
            if (in_array($name, array('attributesToRetrieve', 'type'), true)) {
                if (is_array($value)) {
                    $options[$name] = implode(',', $value);
                }
            }
        }

        return $options;
    }

    /**
     * @param string $optionName
     *
     * @return string
     */
    private function getOptionType($optionName)
    {
        if ($this->isValidHeaderName($optionName)) {
            return 'headers';
        } elseif (in_array($optionName, $this->validQueryParameters, true)) {
            return 'query';
        } elseif (in_array($optionName, array('connectTimeout', 'readTimeout', 'writeTimeout'), true)) {
            return $optionName;
        } else {
            return 'body';
        }
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    private function isValidHeaderName($name)
    {
        if (preg_match('/^X-[a-zA-Z-]+/', $name)) {
            return true;
        }

        if (in_array($name, $this->validHeaders, true)) {
            return true;
        }

        return false;
    }
}
