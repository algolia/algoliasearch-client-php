<?php

namespace Algolia\AlgoliaSearch\Tests\API;

use PHPUnit\Framework\Constraint\Constraint;

class MethodConsistentConstraint extends Constraint
{
    private $instance;

    public function __construct($instance)
    {
        parent::__construct();
        $this->instance = $instance;
    }

    public function evaluate($other, $description = '', $returnResult = false)
    {
        if (!method_exists($this->instance, $other['method'])) {
            $description = 'The method '.$other['method'].' is not implemented.';

            return $returnResult ? false : $this->fail($other, $description);
        }

        $args = (new \ReflectionMethod($this->instance, $other['method']))->getParameters();

        $success = true;
        foreach ($args as $arg) {
            if (!isset($other['args'][$arg->getPosition()])) {
                $success = false;
                $description = 'The parameter '.$arg->getName().' #'.$arg->getPosition().' is missing in '.$other['method'];
                break;
            }

            $argDef = $other['args'][$arg->getPosition()];

            if ($arg->getName() !== $argDef['name']) {
                $success = false;
                $description = 'The parameter '.$arg->getName().' should be named '.$argDef['name'];
                break;
            }

            if (isset($argDef['default'])) {
                if (!$arg->isOptional() && $arg->getDefaultValue() != $argDef['default']) {
                    $success = false;
                    $description = 'The parameter '.$arg->getName().' should have '.print_r($argDef['default'], true).' as a default value';
                    break;
                }
            } else {
                if ($arg->isOptional()) {
                    $success = false;
                    $description = 'The parameter '.$arg->getName().' shouldn\' have default value';
                    break;
                }
            }
        }

        if (!$success) {
            return $returnResult ? false : $this->fail($other, $description);
        }

        return true;
    }

    public function toString()
    {
        return '';
    }

    protected function failureDescription($other)
    {
        return sprintf(
            'the object %s has the method %s correctly implemented',
            get_class($this->instance),
            $other['method']
        );
    }
}
