<?php

namespace Algolia\AlgoliaSearch\Exceptions;

final class DeserializationException extends \InvalidArgumentException
{
    use HasCorrelationId;

    /**
     * @param string          $message
     * @param int             $code
     * @param null|\Throwable $previous
     * @param null|string     $correlationId the `Correlation-ID` returned by the failing response
     */
    public function __construct($message = '', $code = 0, $previous = null, $correlationId = null)
    {
        parent::__construct($this->attachCorrelationId($message, $correlationId), $code, $previous);
    }
}
