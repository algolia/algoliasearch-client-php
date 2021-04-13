<?php

namespace Algolia\AlgoliaSearch\Response;

final class NullResponse extends AbstractResponse
{
    public function __construct()
    {
        $this->apiResponse = [];
    }

    public function wait($requestOptions = [])
    {
        return $this;
    }
}
