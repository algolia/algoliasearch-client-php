<?php

namespace Algolia\AlgoliaSearch\Exceptions;

use Throwable;

final class InvalidArgumentObjectsException extends AlgoliaException
{
    /**
     * InvalidArgumentObjectsException constructor.
     *
     * @param string    $message  The Exception message to throw
     * @param int       $code     The Exception code
     * @param Throwable $previous The previous throwable used for the exception chaining
     */
    public function __construct($message = '', $code = 0, $previous = null)
    {
        if ('' === $message) {
            $message = 'Please provide an array of objects instead of a single object.';
        }

        parent::__construct($message, $code, $previous);
    }
}
