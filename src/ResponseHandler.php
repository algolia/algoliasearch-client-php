<?php

namespace Algolia\AlgoliaSearch;

use GuzzleHttp\Exception\BadResponseException;
use Psr\Http\Message\ResponseInterface;

class ResponseHandler
{
    public function handle(ResponseInterface $response)
    {
        $code = $response->getStatusCode();

        // TODO: handle all status codes with helpful error message and links
        if ($code > 499) {
            throw new \Exception((string) $response->getBody());
        } elseif ($code > 399) {
            throw new \Exception((string) $response->getBody());
        }

        return \GuzzleHttp\json_decode($response->getBody(), true);
    }
}
