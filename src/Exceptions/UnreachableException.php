<?php

namespace Algolia\AlgoliaSearch\Exceptions;

class UnreachableException extends AlgoliaException
{
    public function __construct($message = '', $code = 0, $previous = null)
    {
        if (!$message) {
            $message = 'Impossible to connect, please check your Algolia Application Id. Troubleshooting: https://algo.li/unreachable-php';
        }

        parent::__construct($message, $code, $previous);
    }
}
