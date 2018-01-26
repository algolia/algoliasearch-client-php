<?php

namespace Algolia\AlgoliaSearch;

use Algolia\AlgoliaSearch\Contracts\ClientInterface;

class Client implements ClientInterface
{
    /**
     * @var ApiWrapper
     */
    private $api;

    public function __construct(ApiWrapper $apiWrapper)
    {
        $this->api = $apiWrapper;
    }
    public function listIndices($requestOptions = [])
    {
        return $this->api->get('/1/indexes/', $requestOptions);
    }
}
