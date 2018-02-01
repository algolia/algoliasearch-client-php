<?php

namespace Algolia\AlgoliaSearch\Http;

use Algolia\AlgoliaSearch\Contracts\HttpClientInterface;
use Algolia\AlgoliaSearch\Contracts\TimeoutsInterface;
use GuzzleHttp\Client as GuzzleClient;
use Http\Client\Exception as HttplugException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Guzzle6Adapter implements HttpClientInterface
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
    
    public function send(RequestInterface $request, $timeout, $connectTimeout): ResponseInterface
    {
        try {
            return $this->client->send($request, [
                'timeout' => $timeout,
                'connect_timeout' => $connectTimeout,
            ]);
        } catch (\Exception $e) {
            $exception = $this->handleException($e, $request);
            dump(get_class($exception));
            throw $exception;
        }
    }
    
    private static function buildClient(array $config = [])
    {
        $handlerStack = new HandlerStack(\GuzzleHttp\choose_handler());
        $handlerStack->push(Middleware::prepareBody(), 'prepare_body');
        $config = array_merge(['handler' => $handlerStack], $config);

        return new GuzzleClient($config);
    }

    /**
     * Converts a Guzzle exception into an Httplug exception.
     *
     * @param \Exception $exception
     * @param RequestInterface                 $request
     *
     * @return HttplugException
     */
    private function handleException(\Exception $exception, RequestInterface $request)
    {
        // https://github.com/php-http/guzzle6-adapter/blob/master/src/Promise.php#L106-L139

        if ($exception instanceof GuzzleExceptions\SeekException) {
            return new HttplugException\RequestException($exception->getMessage(), $request, $exception);
        }
        if ($exception instanceof GuzzleExceptions\ConnectException) {
            return new HttplugException\NetworkException($exception->getMessage(), $exception->getRequest(), $exception);
        }
        if ($exception instanceof GuzzleExceptions\RequestException) {
            // Make sure we have a response for the HttpException
            if ($exception->hasResponse()) {
                return new HttplugException\HttpException(
                    $exception->getMessage(),
                    $exception->getRequest(),
                    $exception->getResponse(),
                    $exception
                );
            }
            return new HttplugException\RequestException($exception->getMessage(), $exception->getRequest(), $exception);
        }

        return new HttplugException\TransferException($exception->getMessage(), 0, $exception);
    }
}
