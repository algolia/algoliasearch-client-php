<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
;

return PhpCsFixer\Config::create()
    ->setRules(array(
        '@Symfony' => true,
        'array_syntax' => array('syntax' => 'long'),
    ))
    ->setFinder($finder)
;
