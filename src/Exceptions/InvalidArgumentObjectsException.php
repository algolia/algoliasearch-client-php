<?php

namespace Algolia\AlgoliaSearch\Exceptions;

final class InvalidArgumentObjectsException extends AlgoliaException
{
    public function __construct($message = '', $code = 0, $previous = null)
    {
        if (!$message) {
            $message = 'Please provide an array of objects instead of a single object.';
        }

        parent::__construct($message, $code, $previous);
    }
}
