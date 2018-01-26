<?php

namespace Algolia\AlgoliaSearch;

use Http\Client\Exception;
use Http\Client\HttpClient;
use Http\Message\MessageFactory;
use Psr\Http\Message\ResponseInterface;

class ApiWrapper
{
    private $applicationId;

    private $apiKey;

    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * @var MessageFactory
     */
    private $messageFactory;

    /**
     * @var ResponseHandler
     */
    private $responseHandler;

    private $validHeaders = [
        'X-Algolia-Application-Id',
        'X-Algolia-API-Key',
        'X-Forwarded-For',
        'X-Algolia-UserToken',
        'X-Forwarded-API-Key',
        'Content-type',
    ];

    public function __construct($applicationId, $apiKey, HttpClient $httpClient, MessageFactory $messageFactory)
    {
        $this->applicationId = $applicationId;
        $this->apiKey = $apiKey;

        $this->httpClient = $httpClient;
        $this->messageFactory = $messageFactory;

        // TODO: Inject properly
        $this->responseHandler = new ResponseHandler();
    }

    public function get($endpoint, $requestOptions = [], $urlParams = [])
    {
        return $this->request('GET', $endpoint, $requestOptions, $urlParams);
    }

    protected function request($method, $endpoint, $requestOptions = [], $urlParams = [])
    {
        [$headers, $body] = $this->splitRequestOptions($requestOptions);

        try {
            // TODO: Use an super cool custom UriBuilder
            $uri = $this->gimmeUri($endpoint, $urlParams);

            $request = $this->messageFactory->createRequest($method, $uri, $headers, $body);
            $response = $this->httpClient->sendRequest($request);
        } catch (\Exception $e) {
            dump($e);die;
        } catch (Exception $e) {
            dump($e);die;
        } finally {
            return $this->formatResponse($response);
        }
    }

    protected function splitRequestOptions($requestOptions)
    {
        $headers = [
            'X-Algolia-Application-Id' => $this->applicationId,
            'X-Algolia-API-Key' => $this->apiKey,
        ];

        $body = [];
        foreach ($requestOptions as $optionName => $optionValue) {
            if ($this->isValidHeader($optionName)) {
                $headers[$optionName] = $optionValue;
            } else {
                $body[$optionName] = $optionValue;
            }
        }

        // TODO: Set default headers

        return [$headers, \GuzzleHttp\json_encode($body)]; // TODO: build my own json_encode
    }

    private function isValidHeader($optionName)
    {
        return in_array($optionName, $this->validHeaders);
    }

    private function gimmeUri($endpoint, $urlParams)
    {
        $host = 'https://'.$this->applicationId.'-dsn.algolia.net';

        foreach ($urlParams as $key => $value) {
            if (gettype($value) == 'array') {
                $urlParams[$key] = json_encode($value);
            }
        }

        return $host.$endpoint.'?'.http_build_query($urlParams);
    }
}
