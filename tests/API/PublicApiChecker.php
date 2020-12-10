<?php

declare(strict_types=1);

namespace Algolia\AlgoliaSearch\Tests\API;

use PHPUnit\Framework\Assert;

class PublicApiChecker extends Assert
{
    private $instance;

    private $definition;

    public function __construct($instance, $definition)
    {
        $this->instance = $instance;
        $this->definition = $definition;
    }

    public function check()
    {
        $methodExistsConstraint = new MethodConsistentConstraint($this->instance);

        foreach ($this->definition as $definition) {
            $this->assertThat($definition, $methodExistsConstraint);
        }
    }
}
