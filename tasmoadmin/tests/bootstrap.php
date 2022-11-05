<?php

declare(strict_types=1);

require_once __DIR__.'/../vendor/autoload.php';

const FIXTURE_PATH = __DIR__.'/fixtures/';

function __(string $string, ?string $category = null, array $args = []): string
{
    $cat = '';
    if (isset($category) && !empty($category)) {
        $cat = $category.'_';
    }

    return sprintf('%s%s: %s', $cat, $string, implode(',', $args));
}
