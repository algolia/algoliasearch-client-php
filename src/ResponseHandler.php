<?php

namespace Algolia\AlgoliaSearch;

use Psr\Http\Message\ResponseInterface;

class ResponseHandler
{
    public function handle(ResponseInterface $response)
    {
        if (is_null($response)) {
            throw new \Exception('unreachable hosts');
        }

        // TODO: handle all status codes
        if ($response->getStatusCode() != 200) {
            throw new \Exception((string) $response->getBody());
        }

        return \GuzzleHttp\json_decode($response->getBody(), true);
    }
}
