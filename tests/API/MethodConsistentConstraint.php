<?php

declare(strict_types=1);

namespace Algolia\AlgoliaSearch\Tests\API;

class MethodConsistentConstraint extends \PHPUnit\Framework\Constraint\Constraint
{
    private $instance;

    public function __construct($instance)
    {
        $this->instance = $instance;
    }

    public function evaluate($definition, string $description = '', bool $returnResult = false): ?bool
    {
        if (!method_exists($this->instance, $definition['method'])) {
            $description = 'The method '.$definition['method'].' is not implemented.';

            if ($returnResult) {
                return false;
            }

            $this->fail($definition, $description);
        }

        $refl = new \ReflectionMethod($this->instance, $definition['method']);
        $argsImplemented = $refl->getParameters();

        if (count($argsImplemented) != count($definition['args'])) {
            if ($returnResult) {
                return false;
            }

            $this->fail($definition, 'The method '.$definition['method'].' has a wong number of arguments.');
        }
        $success = true;
        foreach ($argsImplemented as $arg) {
            if (!isset($definition['args'][$arg->getPosition()])) {
                $success = false;
                $description = 'The parameter '.$arg->getName().' #'.$arg->getPosition().' is missing in '.$definition['method'];

                break;
            }

            $argDef = $definition['args'][$arg->getPosition()];

            if ($arg->getName() !== $argDef['name']) {
                $success = false;
                $description = 'The parameter '.$arg->getName().' should be named '.$argDef['name'];

                break;
            }

            if (isset($argDef['default'])) {
                try {
                    $default = $arg->getDefaultValue();
                } catch (\ReflectionException $e) {
                    $default = 'Oops'.mt_rand(1, 12);
                }
                if ($default != $argDef['default']) {
                    $success = false;
                    $description = 'The parameter '.$arg->getName().' should have '.print_r($argDef['default'], true).' as a default value';

                    break;
                }
            } else {
                if ($arg->isOptional()) {
                    $success = false;
                    $description = 'The parameter '.$arg->getName().' shouldn\'t have default value';

                    break;
                }
            }
        }

        if (!$success) {
            if ($returnResult) {
                return false;
            }

            $this->fail($definition, $description);
        }

        return true;
    }

    public function toString(): string
    {
        return '';
    }

    protected function failureDescription($other): string
    {
        return sprintf(
            'the object %s has the method %s correctly implemented',
            get_class($this->instance),
            $other['method']
        );
    }
}
