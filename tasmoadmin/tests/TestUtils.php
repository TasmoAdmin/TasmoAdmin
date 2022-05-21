<?php

namespace Tests\TasmoAdmin;

class TestUtils
{
    public static function getFixturePath(string $path): string
    {
        return FIXTURE_PATH . $path;
    }

    public static function loadFixture(string $path): string
    {
        return file_get_contents(self::getFixturePath($path));
    }
}
