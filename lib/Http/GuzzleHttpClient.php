<?php

namespace Algolia\AlgoliaSearch\Http;

use Algolia\AlgoliaSearch\Exceptions\TimeoutException;
use Algolia\AlgoliaSearch\Http\Psr7\Response;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ConnectTimeoutException;
use GuzzleHttp\Exception\HandlerClosedException;
use GuzzleHttp\Exception\NetworkTimeoutException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ResponseTimeoutException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Utils;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Message\RequestInterface;

final class GuzzleHttpClient implements HttpClientInterface
{
    private $client;

    public function __construct(?GuzzleClient $client = null)
    {
        $this->client = $client ?: self::buildClient();
    }

    public function sendRequest(
        RequestInterface $request,
        $timeout,
        $connectTimeout
    ) {
        try {
            $response = $this->client->send($request, [
                'timeout' => $timeout,
                'connect_timeout' => $connectTimeout,
                'decode_content' => 'gzip',
            ]);
        } catch (HandlerClosedException|NetworkExceptionInterface|ResponseTimeoutException $e) {
            throw new TimeoutException(self::isTimeout($e) ? 'Connection timed out' : $e->getMessage(), 0, $e);
        } catch (RequestException $e) {
            $response = method_exists($e, 'getResponse') ? $e->getResponse() : null;
            if (null !== $response) {
                return $response;
            }

            return new Response(0, [], null, '1.1', $e->getMessage());
        }

        return $response;
    }

    private static function isTimeout(\Throwable $e): bool
    {
        if ($e instanceof ResponseTimeoutException
            || $e instanceof ConnectTimeoutException
            || $e instanceof NetworkTimeoutException) {
            return true;
        }

        return method_exists($e, 'getHandlerContext')
            && CURLE_OPERATION_TIMEDOUT === ($e->getHandlerContext()['errno'] ?? 0);
    }

    private static function buildClient(array $config = [])
    {
        $handlerStack = new HandlerStack(Utils::chooseHandler());
        $handlerStack->push(Middleware::prepareBody(), 'prepare_body');
        $config = array_merge(['handler' => $handlerStack], $config);

        return new GuzzleClient($config);
    }
}
