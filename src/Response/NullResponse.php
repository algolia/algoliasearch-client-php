<?php

namespace Algolia\AlgoliaSearch\Response;

final class NullResponse extends AbstractResponse
{
    /**
     * NullResponse constructor.
     */
    public function __construct()
    {
        $this->apiResponse = array();
    }

    /**
     * @param array $requestOptions
     *
     * @return $this
     */
    public function wait($requestOptions = array())
    {
        return $this;
    }
}
