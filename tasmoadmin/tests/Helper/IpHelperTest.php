<?php

namespace Tests\TasmoAdmin\Helper;

use InvalidArgumentException;
use TasmoAdmin\Helper\IpHelper;
use PHPUnit\Framework\TestCase;

class IpHelperTest extends TestCase
{
    public function testFetchIps(): void
    {
        $ipHelper = new IpHelper();
        $ips = $ipHelper->fetchIps('127.0.0.1', '127.0.0.3');

        self::assertCount(3, $ips);
    }

    public function testFetchIpsSingleIp(): void
    {
        $ipHelper = new IpHelper();
        $ips = $ipHelper->fetchIps('127.0.0.1', '127.0.0.1');

        self::assertCount(1, $ips);
        self::assertEquals('127.0.0.1', $ips[0]);
    }

    public function testFetchIpsSkipped(): void
    {
        $ipHelper = new IpHelper();
        $ips = $ipHelper->fetchIps('127.0.0.1', '127.0.0.4', ['127.0.0.1']);

        self::assertCount(3, $ips);
        self::assertNotContains('127.0.0.1', $ips);
    }

    public function testFetchIpsInvalidFromIp(): void
    {
        self::expectException(InvalidArgumentException::class);
        $ipHelper = new IpHelper();
        $ipHelper->fetchIps('foo', '127.0.0.4');
    }

    public function testFetchIpsInvalidToIp(): void
    {
        self::expectException(InvalidArgumentException::class);
        $ipHelper = new IpHelper();
        $ipHelper->fetchIps('127.0.0.1', 'bar');
    }
}
