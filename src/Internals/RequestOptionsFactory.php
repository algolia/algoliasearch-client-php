<?php

namespace Algolia\AlgoliaSearch\Internals;

class RequestOptionsFactory
{
    private $appId;

    private $apiKey;

    private $validQueryParameters = array('forwardToReplicas');

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
        return new RequestOptions($this->normalize($options));
    }

    public function createBodyLess($options)
    {
        $normalized = $this->normalize($options);
        $normalized['query'] = array_merge($normalized['query'], $normalized['body']);
        $normalized['body'] = array();

        return new RequestOptions($normalized);
    }

    protected function normalize($options)
    {
        $normalized = array(
            'headers' => array(
                'X-Algolia-Application-Id' => $this->appId,
                'X-Algolia-API-Key' => $this->apiKey,
            ),
            'query' => array(),
            'body' => array(),
            'readTimeout' => 5,
            'writeTimeout' => 5,
            'connectTimeout' => 2,
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

    private function getOptionType($optionName)
    {
        if (in_array($optionName, $this->validHeaders)) {
            return 'headers';
        } elseif (in_array($optionName, $this->validQueryParameters)) {
            return 'query';
        } elseif (in_array($optionName, array('connectTimeout', 'readTimeout', 'writeTimeout'))) {
            return $optionName;
        } else {
            return 'body';
        }
    }

    private function removeEmptyValue($normalized)
    {
        foreach (array('headers', 'query', 'body') as $category) {
            foreach ($normalized[$category] as $key => $value) {
                if (empty($value)) {
                    unset($normalized[$category][$key]);
                }
            }
        }

        return $normalized;
    }
}
