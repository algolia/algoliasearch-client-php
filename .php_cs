<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
;

return PhpCsFixer\Config::create()
    ->setRules(array(
        '@Symfony' => true,
        'increment_style' => array('style' => 'post'),
    ))
    ->setFinder($finder)
    ;
