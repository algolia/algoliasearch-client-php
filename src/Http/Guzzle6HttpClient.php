<?php

namespace Algolia\AlgoliaSearch\Http;

use Algolia\AlgoliaSearch\Http\Psr7\Response;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException as GuzzleRequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;

final class Guzzle6HttpClient implements HttpClientInterface
{
    private $client;

    public function __construct(GuzzleClient $client = null)
    {
        $this->client = $client ?: static::buildClient();
    }

    public function sendRequest(RequestInterface $request, $timeout, $connectTimeout)
    {
        try {
            $response = $this->client->send($request, [
                'timeout' => $timeout,
                'connect_timeout' => $connectTimeout,
            ]);
        } catch (GuzzleRequestException $e) {
            if ($e->hasResponse()) {
                return $e->getResponse();
            } else {
                return new Response(
                    0,
                    [],
                    null,
                    '1.1',
                    $e->getMessage()
                );
            }
        }

        return $response;
    }

    private static function buildClient(array $config = [])
    {
        $handlerStack = new HandlerStack(\GuzzleHttp\choose_handler());
        $handlerStack->push(Middleware::prepareBody(), 'prepare_body');
        $config = array_merge(['handler' => $handlerStack], $config);

        return new GuzzleClient($config);
    }
}
