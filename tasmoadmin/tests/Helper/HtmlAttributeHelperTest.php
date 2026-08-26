<?php

namespace Tests\TasmoAdmin\Helper;

use PHPUnit\Framework\TestCase;
use TasmoAdmin\Helper\HtmlAttributeHelper;

class HtmlAttributeHelperTest extends TestCase
{
    public function testEscapeHandlesTextAndHtmlAttributeDelimiters(): void
    {
        self::assertSame('&quot;&lt;script&gt;&amp;', HtmlAttributeHelper::escape('"<script>&'));
    }

    public function testSelectedReturnsHtmlAttributeWhenSelected(): void
    {
        self::assertSame('selected="selected"', HtmlAttributeHelper::selected(true));
    }

    public function testSelectedReturnsEmptyStringWhenNotSelected(): void
    {
        self::assertSame('', HtmlAttributeHelper::selected(false));
    }
}
