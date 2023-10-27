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
    ])
    ->setFinder($finder)
;

return $config;
