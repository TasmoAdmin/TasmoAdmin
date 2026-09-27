<?php

namespace Tests\TasmoAdmin;

use PHPUnit\Framework\TestCase;
use TasmoAdmin\PasskeyRepository;

class PasskeyRepositoryTest extends TestCase
{
    private string $file;

    protected function setUp(): void
    {
        $this->file = sys_get_temp_dir().'/tasmoadmin-passkeys-'.uniqid().'/passkeys.json';
    }

    protected function tearDown(): void
    {
        @unlink($this->file);
        @rmdir(dirname($this->file));
    }

    public function testEmptyWhenFileMissing(): void
    {
        self::assertSame([], new PasskeyRepository($this->file)->all());
    }

    public function testAddFindMarkUsedAndRemove(): void
    {
        $repository = new PasskeyRepository($this->file);
        $repository->add('cred-1', 'PEM', 0, '  Laptop ');

        $entry = $repository->find('cred-1');
        self::assertNotNull($entry);
        self::assertSame('Laptop', $entry['name']);
        self::assertNull($entry['lastUsedAt']);
        self::assertSame('600', substr(sprintf('%o', fileperms($this->file)), -3));

        $repository->markUsed('cred-1', 7);
        $entry = $repository->find('cred-1');
        self::assertSame(7, $entry['signCount']);
        self::assertIsInt($entry['lastUsedAt']);

        self::assertNull($repository->find('unknown'));
        self::assertTrue($repository->remove('cred-1'));
        self::assertFalse($repository->remove('cred-1'));
        self::assertSame([], $repository->all());
    }

    public function testBlankNameFallsBackToDefault(): void
    {
        $repository = new PasskeyRepository($this->file);
        $repository->add('cred-1', 'PEM', 0, ' ');

        self::assertSame('Passkey', $repository->find('cred-1')['name']);
    }
}
