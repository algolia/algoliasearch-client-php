<?php

declare(strict_types=1);

namespace Algolia\AlgoliaSearch\Response;

final class NullResponse extends AbstractResponse
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
