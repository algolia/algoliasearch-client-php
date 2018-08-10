<?php

namespace Algolia\AlgoliaSearch\RequestOptions;

use Algolia\AlgoliaSearch\Support\ClientConfig;
use Algolia\AlgoliaSearch\Support\Config;

class RequestOptionsFactory
{
    private $config;

    private $defaultHeaders = array();

    private $validQueryParameters = array(
        'forwardToReplicas',
        'replaceExistingSynonyms',
        'clearExistingRules',
        'getVersion',
    );

    private $validHeaders = array(
        'Content-type',
    );

    public function __construct(ClientConfig $config)
    {
        $this->config = $config;
    }

    public function create($options, $defaults = array())
    {
        if (is_array($options)) {
            $options  += $defaults;
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

        return $options->addDefaultHeaders($this->defaultHeaders);
    }

    public function createBodyLess($options, $defaults = array())
    {
        $options = $this->create($options, $defaults);

        return $options
            ->addQueryParameters($options->getBody())
            ->setBody(array());
    }

    public function setDefaultHeader($headerName, $headerValue)
    {
        $this->defaultHeaders[$headerName] = $headerValue;
        return $this;
    }

    private function normalize($options)
    {
        $normalized = array(
            'headers' => array(
                'X-Algolia-Application-Id' => $this->config->getAppId(),
                'X-Algolia-API-Key' => $this->config->getApiKey(),
                'User-Agent' => Config::getUserAgent(),
            ),
            'query' => array(),
            'body' => array(),
            'readTimeout' => $this->config->getReadTimeout(),
            'writeTimeout' => $this->config->getWriteTimeout(),
            'connectTimeout' => $this->config->getConnectTimeout(),
        );

        foreach ($options as $optionName => $value) {

            $type = $this->getOptionType($optionName);

            if (in_array($type, array('readTimeout', 'writeTimeout', 'connectTimeout'))) {
                $normalized[$type] = $value;
            } else {
                $normalized[$type][$optionName] = $value;
            }
        }

        return $normalized;
    }

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
