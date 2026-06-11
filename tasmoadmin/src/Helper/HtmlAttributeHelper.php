<?php

namespace TasmoAdmin\Helper;

class HtmlAttributeHelper
{
    public static function selected(bool $selected): string
    {
        return $selected ? 'selected="selected"' : '';
    }
}
