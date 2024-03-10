<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in(['src', 'tests', 'includes'])
;

$config = new PhpCsFixer\Config();

$config
    ->setRiskyAllowed(false)
    ->setRules([
        '@PSR2' => true,
        '@PSR12' => true,
        '@PhpCsFixer' => true,
        'php_unit_test_class_requires_covers' => false,
        'php_unit_internal_class' => false,
    ])
    ->setFinder($finder)
;

return $config;
