<?php

namespace Algolia\AlgoliaSearch\Exceptions;

use Throwable;

final class UnreachableException extends AlgoliaException
{
    /**
     * UnreachableException constructor.
     *
     * @param string         $message
     * @param int            $code
     * @param Throwable|null $previous
     */
    public function __construct($message = '', $code = 0, $previous = null)
    {
        if (!$message) {
            $message = 'Impossible to connect, please check your Algolia Application Id.';
        }

        parent::__construct($message, $code, $previous);
    }
}
