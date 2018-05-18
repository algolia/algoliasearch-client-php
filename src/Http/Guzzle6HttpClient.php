<?php

namespace Algolia\AlgoliaSearch\Http;

use Algolia\AlgoliaSearch\Exceptions\BadRequestException;
use Algolia\AlgoliaSearch\Exceptions\RetriableException;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException as GuzzleRequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Guzzle6HttpClient implements HttpClientInterface
{
    private $client;

    public function __construct(GuzzleClient $client = null)
    {
        if (!$client) {
            $client = static::buildClient();
        }

        $this->client = $client;
    }

    public static function createWithConfig(array $config)
    {
        return new self(static::buildClient($config));
    }

    public function createUri($uri)
    {
        return \GuzzleHttp\Psr7\uri_for($uri);
    }

    public function createRequest(
        $method,
        $uri,
        array $headers = array(),
        $body = null,
        $protocolVersion = '1.1'
    ) {
        if (is_array($body)) {
            $body = \GuzzleHttp\json_encode($body);
        }

        return new Request($method, $uri, $headers, $body, $protocolVersion);
    }

    public function sendRequest(RequestInterface $request, $timeout, $connectTimeout, $userAgent)
    {
        try {
            $response = $this->client->send($request, array(
                'timeout' => $timeout,
                'connect_timeout' => $connectTimeout,
                'headers' => array(
                    'User-Agent' => $userAgent,
                ),
            ));

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            $exception = $this->handleException($e, $request);
            throw $exception;
        }
    }

    private static function buildClient(array $config = array())
    {
        $handlerStack = new HandlerStack(\GuzzleHttp\choose_handler());
        $handlerStack->push(Middleware::prepareBody(), 'prepare_body');
        $config = array_merge(array('handler' => $handlerStack), $config);

        return new GuzzleClient($config);
    }

    private function handleException(\Exception $exception, RequestInterface $request)
    {
        if ($exception instanceof GuzzleRequestException) {
            // Make sure we have a response for the HttpException
            if ($exception->hasResponse()) {
                $statusCode = $exception->getResponse()->getStatusCode();
                $body = \GuzzleHttp\json_decode(
                    (string) $exception->getResponse()->getBody(),
                    true
                );

                if ($statusCode >= 500) {
                    throw new \Exception('PANIC', $statusCode);
                } elseif ($statusCode >= 400) {
                    throw new BadRequestException($body['message'], $statusCode);
                }
            }
        }

        return new RetriableException($exception->getMessage(), 0, $exception);
    }

    private function handleResponse(ResponseInterface $response)
    {
        $body = (string) $response->getBody();
        $body = \GuzzleHttp\json_decode($body, true);

        $statusCode = $response->getStatusCode();
        if ($statusCode >= 400) {
            die('TODO: WTF?');
        }

        return $body;
    }
}
