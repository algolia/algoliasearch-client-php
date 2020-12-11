<?php

declare(strict_types=1);

namespace Algolia\AlgoliaSearch\Tests;

use PHPUnit\Framework\Constraint\Constraint;
use SebastianBergmann\Comparator\ComparisonFailure;

final class ArraySubsetConstraint extends Constraint
{
    /**
     * @var iterable
     */
    private $subset;

    /**
     * @var bool
     */
    private $strict;

    public function __construct(iterable $subset, bool $strict = false)
    {
        $this->strict = $strict;
        $this->subset = $subset;
    }

    public function evaluate($other, string $description = '', bool $returnResult = false): ?bool
    {
        //type cast $other & $this->subset as an array to allow
        //support in standard array functions.
        $other = $this->toArray($other);
        $this->subset = $this->toArray($this->subset);

        $patched = \array_replace_recursive($other, $this->subset);

        if ($this->strict) {
            $result = $other === $patched;
        } else {
            $result = $other == $patched;
        }

        if ($returnResult) {
            return true;
        }

        if (!$result) {
            $f = new ComparisonFailure(
                $patched,
                $other,
                \var_export($patched, true),
                \var_export($other, true)
            );

            $this->fail($other, $description, $f);
        }

        return null;
    }

    public function toString(): string
    {
        return 'has the subset '.$this->exporter()->export($this->subset);
    }

    protected function failureDescription($other): string
    {
        return 'an array '.$this->toString();
    }

    private function toArray(iterable $other): array
    {
        if (\is_array($other)) {
            return $other;
        }

        if ($other instanceof \ArrayObject) {
            return $other->getArrayCopy();
        }

        if ($other instanceof \Traversable) {
            return \iterator_to_array($other);
        }

        // Keep BC even if we know that array would not be the expected one
        return (array) $other;
    }
}
