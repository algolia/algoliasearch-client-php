<?php

namespace Algolia\AlgoliaSearch\Exception;

use Throwable;

class UnreachableException extends \Exception
{
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        if (!$message) {
            $message = 'Impossible to connect, please check your Algolia Application Id. Troubleshooting: https://algo.li/unreachable-php';
        }

        parent::__construct($message, $code, $previous);
    }
}
