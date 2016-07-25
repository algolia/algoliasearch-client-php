<?php

namespace AlgoliaSearch\Exception;

use AlgoliaSearch\AlgoliaException;

class AlgoliaIndexNotFoundException extends AlgoliaException
{
    const MESSAGE = 'Index not found: %s.';

    /**
     * IndexNotFoundException constructor.
     *
     * @param string $indexName
     * @param string $message
     * @param int $code
     * @param \Exception|null $previous
     */
    public function __construct($indexName, $message = null, $code = 0, \Exception $previous = null)
    {
        $message = $message ? $message : sprintf(self::MESSAGE, $indexName);
        parent::__construct($message, $code, $previous);
    }
}
