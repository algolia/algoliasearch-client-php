<?php

namespace Algolia\AlgoliaSearch\Http;

use Algolia\AlgoliaSearch\Exceptions\BadRequestException;
use Algolia\AlgoliaSearch\Exceptions\NotFoundException;
use Algolia\AlgoliaSearch\Exceptions\RetriableException;
use Algolia\AlgoliaSearch\Interfaces\ClientConfigInterface;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException as GuzzleRequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Guzzle6HttpClient implements HttpClientInterface
{
    /**
     * The config instance.
     *
     * @var \Algolia\AlgoliaSearch\Interfaces\ClientConfigInterface
     */
    private $config;

    private $client;

    public function __construct(ClientConfigInterface $config, GuzzleClient $client = null)
    {
        $this->config = $config;
        $this->client = $client ?: static::buildClient();
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
            // Send an empty body instead of "[]" in case there are
            // no content/params to send
            $body = empty($body) ? '' : \GuzzleHttp\json_encode($body);
        }

        return new Request($method, $uri, $headers, $body, $protocolVersion);
    }

    public function sendRequest(RequestInterface $request, $timeout, $connectTimeout)
    {
        try {
            $response = $this->client->send($request, array(
                'timeout' => $timeout,
                'connect_timeout' => $connectTimeout,
            ));
        } catch (\Exception $e) {
            throw $this->handleException($e, $request);
        }

        return $this->handleResponse($response);
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
                $contents = (string) $exception->getResponse()->getBody();

                $body = \GuzzleHttp\json_decode($contents, true);

                $this->config->getLogger()->warning('Algolia API client: Request failed.', array(
                    'statusCode' => $statusCode,
                    'message' => $body['message'],
                ));

                if ($statusCode >= 500) {
                    return new RetriableException(
                        'An internal server error occurred on '.$request->getUri()->getHost(),
                        $statusCode,
                        $exception
                    );
                } elseif (404 == $statusCode) {
                    throw new NotFoundException($body['message'], $statusCode);
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
        $statusCode = $response->getStatusCode();

        if ('' === $body && 204 == $statusCode) {
            return '';
        }

        return \GuzzleHttp\json_decode($body, true);
    }
}
