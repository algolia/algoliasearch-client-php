<?php

namespace Algolia\AlgoliaSearch\Internals;

use Algolia\AlgoliaSearch\Config;

class RequestOptionsFactory
{
    private $appId;

    private $apiKey;

    private $validQueryParameters = array(
        'forwardToReplicas',
        'getVersion',
    );

    private $validHeaders = array(
        'X-Algolia-Application-Id',
        'X-Algolia-API-Key',
        'X-Forwarded-For',
        'X-Algolia-UserToken',
        'X-Forwarded-API-Key',
        'Content-type',
    );

    public function __construct($appId, $apiKey)
    {
        $this->appId = $appId;
        $this->apiKey = $apiKey;
    }

    public function create($options)
    {
        $options = $this->format($options);

        return new RequestOptions($this->normalize($options));
    }

    public function createBodyLess($options)
    {
        $normalized = $this->normalize($options);
        $normalized['query'] = array_merge($normalized['query'], $normalized['body']);
        $normalized['body'] = array();

        return new RequestOptions($normalized);
    }

    private function normalize($options)
    {
        $normalized = array(
            'headers' => array(
                'X-Algolia-Application-Id' => $this->appId,
                'X-Algolia-API-Key' => $this->apiKey,
            ),
            'query' => array(),
            'body' => array(),
            'readTimeout' => Config::getReadTimeout(),
            'writeTimeout' => Config::getWriteTimeout(),
            'connectTimeout' => Config::getConnectTimeout(),
        );

        foreach ($options as $optionName => $value) {
            $type = $this->getOptionType($optionName);

            if (in_array($type, array('readTimeout', 'writeTimeout', 'connectTimeout'))) {
                $normalized[$type] = $value;
            } else {
                $normalized[$type][$optionName] = $value;
            }
        }

        $normalized = $this->removeEmptyValue($normalized);

        return $normalized;
    }

    private function format($options)
    {
        foreach ($options as $name => $value) {
            if (in_array($name, array('attributesToRetrieve'), true)) {
                if (is_array($value)) {
                    $options[$name] = implode(',', $value);
                }
            }
        }

        return $options;
    }

    private function getOptionType($optionName)
    {
        if (is_int($optionName)) {
            return 'body';
        } elseif (in_array($optionName, $this->validHeaders, true)) {
            return 'headers';
        } elseif (in_array($optionName, $this->validQueryParameters, true)) {
            return 'query';
        } elseif (in_array($optionName, array('connectTimeout', 'readTimeout', 'writeTimeout'), true)) {
            return $optionName;
        } else {
            return 'body';
        }
    }

    private function removeEmptyValue($normalized)
    {
        foreach (array('headers', 'query', 'body') as $category) {
            foreach ($normalized[$category] as $key => $value) {
                if ($this->isEmpty($value)) {
                    unset($normalized[$category][$key]);
                }
            }
        }

        return $normalized;
    }

    /**
     * PHP native empty() function will consider int(0) or bool(false) empty
     * but `forwardToReplica = false` or `page = 0` are meaningful and need to be sent
     * to the API.
     */
    private function isEmpty($value)
    {
        if (is_array($value) || is_string($value)) {
            return empty($value);
        }

        return false;
    }
}
