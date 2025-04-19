<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in(['src', 'tests', 'includes', 'pages'])
;

$config = new PhpCsFixer\Config();

$config
    ->setRiskyAllowed(false)
    ->setRules([
        '@PHP84Migration' => true,
        '@PSR2' => true,
        '@PSR12' => true,
        '@PhpCsFixer' => true,
        'php_unit_test_class_requires_covers' => false,
        'php_unit_internal_class' => false,
    ])
    ->setFinder($finder)
;

return $config;
