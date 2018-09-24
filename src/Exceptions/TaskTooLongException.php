<?php

namespace Algolia\AlgoliaSearch\Exceptions;

class TaskTooLongException extends AlgoliaException
{
    public function __construct($message = "", $code = 0, \Exception $previous = null)
    {
        if ("" === $message) {
            $message = 'Task took too long to complete. You can increase $waitTaskTimeBeforeRetry and $waitTaskMaxRetry in ClientConfig.';
        }
        parent::__construct($message, $code, $previous);
    }
}
