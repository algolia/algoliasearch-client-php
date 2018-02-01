<?php

namespace Algolia\AlgoliaSearch\Internals;

use Algolia\AlgoliaSearch\Contracts\HttpClientInterface;
use Algolia\AlgoliaSearch\Exception\UnreachableException;
use function GuzzleHttp\Psr7\build_query;
use Http\Client\Exception\NetworkException;
use Http\Client\Exception\TransferException;
use Http\Message\MessageFactory;
use Http\Message\UriFactory;

class ApiWrapper
{
    /**
     * @var ClusterHosts
     */
    private $clusterHosts;

    /**
     * @var RequestOptionsFactory
     */
    private $requestOptionsFactory;

    /**
     * @var GuzzleHttpClient
     */
    private $http;

    /**
     * @var MessageFactory
     */
    private $messageFactory;

    /**
     * @var UriFactory
     */
    private $uriFactory;

    /**
     * @var ResponseHandler
     */
    private $responseHandler;

    public function __construct(
        ClusterHosts $clusterHosts,
        RequestOptionsFactory $requestOptionsFactory,
        HttpClientInterface $http,
        MessageFactory $messageFactory,
        UriFactory $uriFactory
    ) {
        $this->clusterHosts = $clusterHosts;
        $this->requestOptionsFactory = $requestOptionsFactory;

        $this->http = $http;
        $this->messageFactory = $messageFactory;
        $this->uriFactory = $uriFactory;

        // TODO: Inject properly
        $this->responseHandler = new ResponseHandler();
    }

    public function get($path, $requestOptions = [])
    {
        $requestOptions = $this->requestOptionsFactory->createBodyLess($requestOptions);

        return $this->request(
            'GET',
            $path,
            $requestOptions->getHeaders(),
            $requestOptions->getQuery(),
            []
        );
    }

    public function post($path, $requestOptions = [])
    {
        $requestOptions = $this->requestOptionsFactory->create($requestOptions);

        return $this->request(
            'POST',
            $path,
            $requestOptions->getHeaders(),
            $requestOptions->getQuery(),
            $requestOptions->getBody()
        );
    }

    private function request($method, $path, array $headers = [], array $query = [], array $body = [])
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

                $response = $this->http->send($request, 10, 10);

                return $this->responseHandler->handle($response);
            } catch (NetworkException $e) {
                $this->clusterHosts->failed($host);
            } catch (TransferException $e) {
                $this->clusterHosts->failed($host);
            } catch (\Exception $e) {
                // TODO: panic
                var_dump($e->getMessage());
                die;
            }
        }

        throw new UnreachableException();
    }
}
