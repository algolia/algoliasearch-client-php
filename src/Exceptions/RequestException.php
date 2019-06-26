<?php

namespace Algolia\AlgoliaSearch\Exceptions;

use Psr\Http\Message\RequestInterface;

class RequestException extends AlgoliaException
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param RequestInterface $request
     *
     * @return $this
     */
    public function setRequest(RequestInterface $request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * @return RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }
}
