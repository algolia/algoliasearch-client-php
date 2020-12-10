<?php

declare(strict_types=1);

namespace Algolia\AlgoliaSearch\Tests;

use ArrayAccess;
use Exception;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

/**
 * @mixin TestCase
 */
trait AssertArraySubsetTrait
{
    /**
     * Asserts that an array has a specified subset.
     *
     * @param array|ArrayAccess $subset
     * @param array|ArrayAccess $array
     *
     * @throws ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     *
     * @codeCoverageIgnore
     */
    public static function assertArraySubset($subset, $array, bool $checkForObjectIdentity = false, string $message = ''): void
    {
        if (!(\is_array($subset) || $subset instanceof ArrayAccess)) {
            throw new InvalidArgumentException(1, 'array or ArrayAccess');
        }

        if (!(\is_array($array) || $array instanceof ArrayAccess)) {
            throw new InvalidArgumentException(2, 'array or ArrayAccess');
        }

        $constraint = new ArraySubsetConstraint($subset, $checkForObjectIdentity);

        static::assertThat($array, $constraint, $message);
    }
}
