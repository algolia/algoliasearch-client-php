<?php

namespace Algolia\AlgoliaSearch\Http;

use Algolia\AlgoliaSearch\Exceptions\AlgoliaException;
use Algolia\AlgoliaSearch\Exceptions\BadRequestException;
use Algolia\AlgoliaSearch\Exceptions\NotFoundException;
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
        } catch (GuzzleRequestException $e) {
            if ($e->hasResponse()) {
                return $this->handleResponse($e->getResponse(), $request);
            }

            throw $this->handleException($e, $request);
        } catch (\Exception $e) {
            throw $this->handleException($e, $request);
        }

        return $this->handleResponse($response, $request);
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
        return new RetriableException($exception->getMessage(), 0, $exception);
    }

    private function handleResponse(ResponseInterface $response, RequestInterface $request)
    {
        $body = (string) $response->getBody();
        $statusCode = $response->getStatusCode();

        if ($statusCode >= 500) {
            throw new RetriableException(
                'An internal server error occurred on '.$request->getUri()->getHost(),
                $statusCode
            );
        }

        $responseArray = \GuzzleHttp\json_decode($body, true);

        if (404 == $statusCode) {
            throw new NotFoundException($responseArray['message'], $statusCode);
        } elseif ($statusCode >= 400) {
            throw new BadRequestException($responseArray['message'], $statusCode);
        } elseif (2 != ($statusCode / 100)) {
            throw new AlgoliaException($statusCode.': '.$body, $statusCode);
        }

        return $responseArray;
    }
}
