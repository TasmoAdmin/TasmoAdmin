<?php

namespace TasmoAdmin\Helper;

class EnvironmentHelper
{
    public static function isEnabled(string $variable): bool
    {
        return filter_var(getenv($variable), FILTER_VALIDATE_BOOLEAN);
    }
}
