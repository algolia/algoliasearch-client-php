<?php

namespace Algolia\AlgoliaSearch\Internals;

class RequestOptionsFactory
{
    private $appId;

    private $apiKey;

    private $validQueryParameters = array(
        'forwardToReplicas',
        'replaceExistingSynonyms',
        'clearExistingRules',
        'getVersion',
    );

    private $validHeaders = array(
        'Content-type',
    );

    public function __construct($appId = null, $apiKey = null)
    {
        $this->appId = $appId;
        $this->apiKey = $apiKey;
    }

    public function create($options)
    {
        if ($options instanceof RequestOptions) {
            return $options
                ->addDefaultAppId($this->appId)
                ->addDefaultApiKey($this->apiKey);
        }

        // TODO: Add exception if not array
        $options = $this->format($options);
        $options = $this->normalize($options);
        $options = $this->addDefaultCredentials($options);

        return new RequestOptions($options);
    }

    public function createBodyLess($options)
    {
        if ($options instanceof RequestOptions) {
            return $options
                ->addQueryParameters($options->getBody())
                ->setBody(array())
                ->addDefaultAppId($this->appId)
                ->addDefaultApiKey($this->apiKey);
        }

        // TODO: Add exception if not array
        $options = $this->normalize($options);
        $options['query'] = array_merge($options['query'], $options['body']);
        $options['body'] = array();
        $options = $this->addDefaultCredentials($options);

        return new RequestOptions($options);
    }

    private function normalize($options)
    {
        $normalized = array(
            'headers' => array(),
            'query' => array(),
            'body' => array(),
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

    private function addDefaultCredentials($options)
    {
        $options['headers'] += array(
            'X-Algolia-Application-Id' => $this->appId,
            'X-Algolia-API-Key' => $this->apiKey,
        );

        return $options;
    }
}
