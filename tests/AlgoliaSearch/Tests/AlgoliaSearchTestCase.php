<?php

namespace AlgoliaSearch\Tests;

class AlgoliaSearchTestCase extends \PHPUnit_Framework_TestCase
{
    public function safe_name($name)
    {
        if (getenv('TRAVIS') != 'true') {
            return $name;
        }
        $s = explode('.', getenv('TRAVIS_JOB_NUMBER'));
        $id = end($s);

        return $name.'_travis-'.$id;
    }

    public function containsValue($array, $attr, $value)
    {
        foreach ($array as $elt) {
            if ($elt[$attr] == $value) {
                return true;
            }
        }

        return false;
    }
}
