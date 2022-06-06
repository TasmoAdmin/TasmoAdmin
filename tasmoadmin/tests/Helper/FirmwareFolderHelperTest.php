<?php

namespace Tests\TasmoAdmin\Helper;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use TasmoAdmin\Helper\FirmwareFolderHelper;
use PHPUnit\Framework\TestCase;

class FirmwareFolderHelperTest extends TestCase
{
    private vfsStreamDirectory $root;

    protected function setUp(): void
    {
    }

    public function testCleanEmpty(): void
    {
        $filesystem = vfsStream::setup('firmware');
        FirmwareFolderHelper::clean($filesystem->url() . '/');
        self::assertEmpty($filesystem->getChildren());
    }

    public function testClean(): void
    {
        $filesystem = vfsStream::setup('firmware', null, ['firmware.bin' => 'contents']);
        FirmwareFolderHelper::clean($filesystem->url() . '/');
        self::assertEmpty($filesystem->getChildren());
    }

    public function testCleanProtected(): void
    {
        $filesystem = vfsStream::setup('firmware', null, ['firmware.bin' => 'contents', '.empty' => '']);
        FirmwareFolderHelper::clean($filesystem->url() . '/');
        self::assertCount(1, $filesystem->getChildren());
    }
}
