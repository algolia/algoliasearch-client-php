<?php

namespace Algolia\AlgoliaSearch\Tests;

use Algolia\AlgoliaSearch\Interfaces\IndexContentInterface;

class ContactIndexContent implements IndexContentInterface
{

    public function getObjects()
    {
        return \Contact::getAll();
    }

    public function getSettings()
    {
        return array(
            'hitsPerPage' => 1000,
            'searchableAttributes' => array('firstname', 'lastname', 'surname', 'address'),
            'customRanking' => array('reputation'),
        );
    }

    public function getSynonyms()
    {
        return json_decode(
            file_get_contents(__DIR__.'/contact-synonyms.json'),
            true
        );
    }

    public function getRules()
    {
        // If you manage rules via the dashboard, return `false`
        // so it will be copied from the production index
        return false;
    }
}
