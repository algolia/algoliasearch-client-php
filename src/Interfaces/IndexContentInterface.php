<?php

namespace Algolia\AlgoliaSearch\Interfaces;

interface IndexContentInterface
{
    public function getObjects();

    public function getSettings();

    public function getSynonyms();

    public function getRules();
}
