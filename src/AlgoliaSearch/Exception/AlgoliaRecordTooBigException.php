<?php

namespace AlgoliaSearch\Exception;

use AlgoliaSearch\AlgoliaException;

class AlgoliaRecordTooBigException extends AlgoliaException
{
    const MESSAGE = 'Record is too big.';

    /**
     * @var array|null
     */
    private $record;

    /**
     * AlgoliaRecordTooBigException constructor.
     *
     * @param string $message
     * @param null $record
     * @param int $code
     * @param \Exception|null $previous
     */
    public function __construct($record = null, $message = self::MESSAGE, $code = 0, \Exception $previous = null)
    {
        $this->record = $record;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return array|null
     */
    public function getRecord()
    {
        return $this->record;
    }
}
