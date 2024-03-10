<?php

namespace Tests\TasmoAdmin\Helper;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use TasmoAdmin\Helper\FirmwareFolderHelper;

class FirmwareFolderHelperTest extends TestCase
{
    public function testCleanEmpty(): void
    {
        $filesystem = vfsStream::setup('firmware');
        FirmwareFolderHelper::clean($filesystem->url().'/');
        self::assertEmpty($filesystem->getChildren());
    }

    public function testClean(): void
    {
        $filesystem = vfsStream::setup('firmware', null, ['firmware.bin' => 'contents']);
        FirmwareFolderHelper::clean($filesystem->url().'/');
        self::assertEmpty($filesystem->getChildren());
    }

    public function testCleanProtected(): void
    {
        $filesystem = vfsStream::setup('firmware', null, ['firmware.bin' => 'contents', '.empty' => '']);
        FirmwareFolderHelper::clean($filesystem->url().'/');
        self::assertCount(1, $filesystem->getChildren());
    }
}
