<?php

namespace Algolia\AlgoliaSearch\Response;

class NullResponse extends AbstractResponse
{
    public function __construct()
    {
        $this->apiResponse = array();
    }

    public function wait($requestOptions = array())
    {
        return $this;
    }
}
