<?php

namespace AlgoliaSearch\Tests;

use PHPUnit\Framework\TestCase;

class AlgoliaSearchTestCase extends TestCase
{
    public function safe_name($name)
    {
        if (getenv('TRAVIS') != 'true') {
            return $name;
        }

        return 'TRAVIS_php_'.$name.'_job-'.getenv('TRAVIS_JOB_ID');
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
