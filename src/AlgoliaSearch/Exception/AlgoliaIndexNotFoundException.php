<?php

namespace AlgoliaSearch\Exception;

use AlgoliaSearch\AlgoliaException;

class AlgoliaIndexNotFoundException extends AlgoliaException
{
    /**
     * @var string
     */
    private $indexName;

    /**
     * @return string
     */
    public function getIndexName()
    {
        return $this->indexName;
    }

    /**
     * @param string $indexName
     *
     * @return AlgoliaIndexNotFoundException
     */
    public function setIndexName($indexName)
    {
        $this->indexName = $indexName;
        return $this;
    }
}
