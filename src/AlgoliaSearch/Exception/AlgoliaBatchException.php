<?php

namespace AlgoliaSearch\Exception;

use AlgoliaSearch\AlgoliaException;

class AlgoliaBatchException extends AlgoliaException
{
    /**
     * @var array
     */
    private $records = array();

    /**
     * @return array
     */
    public function getRecords()
    {
        return $this->records;
    }

    /**
     * @param array $records
     *
     * @return $this
     */
    public function setRecords($records)
    {
        $this->records = $records;

        return $this;
    }

    /**
     * @param array $records
     *
     * @return $this
     */
    public function addRecords($records)
    {
        $this->records = array_merge($this->records, $records);

        return $this;
    }

    /**
     * @param mixed $record
     *
     * @return $this
     */
    public function addRecord($record)
    {
        $this->records[] = $record;

        return $this;
    }
}
