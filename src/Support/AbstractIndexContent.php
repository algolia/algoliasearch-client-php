<?php

namespace Algolia\AlgoliaSearch\Support;

abstract class AbstractIndexContent
{
    abstract public function getObjects();

    public function getSettings()
    {
        return false;
    }

    public function getSynonyms()
    {
        return false;
    }

    public function getRules()
    {
        return false;
    }
}
