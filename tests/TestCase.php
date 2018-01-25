<?php

namespace Algolia\AlgoliaSearch\Tests;

use PHPUnit\Framework\TestCase as PHPUitTestCase;

abstract class TestCase extends PHPUitTestCase
{
    public function safeName($name)
    {
        return $name;
    }
}
