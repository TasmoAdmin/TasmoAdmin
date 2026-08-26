<?php

namespace TasmoAdmin\Helper;

class HtmlAttributeHelper
{
    public static function escape(bool|float|int|string|null $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function selected(bool $selected): string
    {
        return $selected ? 'selected="selected"' : '';
    }
}
