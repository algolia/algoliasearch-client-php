<?php

namespace Algolia\AlgoliaSearch\Http;

use Algolia\AlgoliaSearch\Exceptions\RetriableException;
use Algolia\AlgoliaSearch\Http\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;

/**
 * Implements experimental integration with Symfony HTTP Client.
 *
 * @see https://github.com/symfony/http-client/
 * @experimental
 */
final class SymfonyHttpClient implements HttpClientInterface
{
    /**
     * @var HttpClient
     */
    private $client;

    public function __construct()
    {
        $this->client = HttpClient::create();
    }

    public function sendRequest(RequestInterface $request, $timeout, $connectTimeout)
    {
        $opts = array(
            'headers' => $request->getHeaders(),
            'body' => (string) $request->getBody(),
            'http_version' => '1.0' === $request->getProtocolVersion() ? '1.0' : null,
        );

        //In Symfony HTTP Client timeouts are abstracted and there's no separate timeouts, so pick higher one
        if ($timeout > $connectTimeout && $timeout > 0) {
            $opts['timeout'] = $timeout;
        } elseif ($connectTimeout > 0) {
            $opts['timeout'] = $connectTimeout;
        }

        $response = $this->client->request($request->getMethod(), (string) $request->getUri(), $opts);

        try {
            //getHeaders() or getContent() will throw if the request failed because of the status code
            return new Response($response->getStatusCode(), $response->getHeaders(), $response->getContent());
        } catch (ServerExceptionInterface $e) {
            throw new RetriableException(
                'An server error occurred on '.$request->getUri()->getHost(),
                $response->getStatusCode(),
                $e
            );
        } catch (ClientExceptionInterface $e) {
            throw new RetriableException(
                'An client error occurred on '.$request->getUri()->getHost(),
                $response->getStatusCode(),
                $e
            );
        } catch (ExceptionInterface $e) {
            throw new RetriableException(
                'An unhandled error occurred on '.$request->getUri()->getHost(),
                $response->getStatusCode(),
                $e
            );
        }
    }
}
