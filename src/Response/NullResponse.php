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
     * {@inheritdoc}
     */
    public function wait($requestOptions = array())
    {
        return $this;
    }
}
