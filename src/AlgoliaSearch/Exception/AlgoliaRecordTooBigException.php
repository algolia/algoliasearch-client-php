<?php

namespace AlgoliaSearch\Exception;

use AlgoliaSearch\AlgoliaException;

class AlgoliaRecordTooBigException extends AlgoliaException
{
    /**
     * @var array|null
     */
    private $record;

    /**
     * @return array|null
     */
    public function getRecord()
    {
        return $this->record;
    }

    /**
     * @param array|null $record
     *
     * @return AlgoliaRecordTooBigException
     */
    public function setRecord($record)
    {
        $this->record = $record;

        return $this;
    }
}
