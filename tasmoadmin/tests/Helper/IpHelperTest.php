<?php

namespace Tests\TasmoAdmin\Helper;

use PHPUnit\Framework\TestCase;
use TasmoAdmin\Helper\IpHelper;

class IpHelperTest extends TestCase
{
    public function testFetchIps(): void
    {
        $ipHelper = new IpHelper();
        $ips = $ipHelper->fetchIps('127.0.0.1', '127.0.0.3');

        self::assertCount(3, $ips);
    }

    public function testFetchIpsMultipleSubnet(): void
    {
        $ipHelper = new IpHelper();
        $ips = $ipHelper->fetchIps('127.0.0.128', '127.0.1.2');

        self::assertCount(131, $ips);
        self::assertEquals('127.0.0.128', $ips[0]);
        self::assertEquals('127.0.1.2', end($ips));
    }

    public function testFetchIpsTooLargeRange(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $ipHelper = new IpHelper();
        $ipHelper->fetchIps('127.0.0.1', '127.255.8.2');
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

    /**
     * @dataProvider provideInvalidIps
     */
    public function testFetchIpsInvalidFromIp(string $invalidIp): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $ipHelper = new IpHelper();
        $ipHelper->fetchIps($invalidIp, '127.0.0.4');
    }

    /**
     * @dataProvider provideInvalidIps
     */
    public function testFetchIpsInvalidToIp(string $invalidIp): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $ipHelper = new IpHelper();
        $ipHelper->fetchIps('127.0.0.1', $invalidIp);
    }

    public static function provideInvalidIps(): array
    {
        return [
            ['foo'],
            ['127.0.0,1'],
        ];
    }
}
