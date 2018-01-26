<?php

namespace Algolia\AlgoliaSearch;

use function GuzzleHttp\Psr7\build_query;
use Http\Client\Exception\NetworkException;
use Http\Client\HttpClient;
use Http\Message\MessageFactory;
use Http\Message\UriFactory;

class ApiWrapper
{
    private $applicationId;

    private $apiKey;

    /**
     * @var ClusterHosts
     */
    private $clusterHosts;

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

    /**
     * @var UriFactory
     */
    private $uriFactory;

    private $validHeaders = [
        'X-Algolia-Application-Id',
        'X-Algolia-API-Key',
        'X-Forwarded-For',
        'X-Algolia-UserToken',
        'X-Forwarded-API-Key',
        'Content-type',
    ];

    public function __construct($applicationId, $apiKey, ClusterHosts $clusterHosts, HttpClient $httpClient, MessageFactory $messageFactory, UriFactory $uriFactory)
    {
        $this->applicationId = $applicationId;
        $this->apiKey = $apiKey;
        $this->clusterHosts = $clusterHosts;

        $this->httpClient = $httpClient;
        $this->messageFactory = $messageFactory;
        $this->uriFactory = $uriFactory;

        // TODO: Inject properly
        $this->responseHandler = new ResponseHandler();
    }

    public function get($path, $requestOptions = [])
    {
        [$headers, $params] = $this->splitRequestOptions($requestOptions);

        return $this->request('GET', $path, $headers, [], $params);
    }

    private function request($method, $path, array $headers = [], array $body = [], array $query = [])
    {
        $uri = $this->uriFactory
            ->createUri($path)
            ->withQuery(build_query($query))
            ->withScheme('https');

        foreach ($this->clusterHosts->all() as $host) {
            try {
                $request = $this->messageFactory->createRequest(
                    $method,
                    $uri->withHost($host),
                    $headers,
                    \GuzzleHttp\json_encode($body)
                );

                $response = $this->httpClient->sendRequest($request);

                return $this->responseHandler->handle($response);
            } catch (NetworkException $e) {
                $this->clusterHosts->failed($host);
            } catch (\Exception $e) {
                // TODO: panic
                var_dump($e->getMessage());die;
            }
        }
    }

    private function splitRequestOptions($requestOptions)
    {
        // TODO: Set other default headers
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

        return [$headers, $body];
    }

    private function isValidHeader($optionName)
    {
        return in_array($optionName, $this->validHeaders);
    }
}
