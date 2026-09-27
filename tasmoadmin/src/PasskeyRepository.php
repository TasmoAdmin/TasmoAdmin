<?php

declare(strict_types=1);

namespace TasmoAdmin;

/**
 * Stores WebAuthn passkey credentials (public keys only) as JSON in the data dir.
 */
class PasskeyRepository
{
    private string $file;

    public function __construct(string $file)
    {
        $this->file = $file;
    }

    /**
     * @return list<array{id:string,publicKey:string,signCount:int,name:string,createdAt:int,lastUsedAt:?int}>
     */
    public function all(): array
    {
        if (!is_file($this->file)) {
            return [];
        }

        $data = json_decode((string) file_get_contents($this->file), true);
        if (!is_array($data)) {
            return [];
        }

        return array_values(array_filter($data, static fn ($entry): bool => is_array($entry)
            && isset($entry['id'], $entry['publicKey'])
            && is_string($entry['id'])
            && is_string($entry['publicKey'])));
    }

    /**
     * @return null|array{id:string,publicKey:string,signCount:int,name:string,createdAt:int,lastUsedAt:?int}
     */
    public function find(string $id): ?array
    {
        foreach ($this->all() as $entry) {
            if (hash_equals($entry['id'], $id)) {
                return $entry;
            }
        }

        return null;
    }

    public function add(string $id, string $publicKey, int $signCount, string $name): void
    {
        $entries = array_values(array_filter($this->all(), static fn (array $entry): bool => $entry['id'] !== $id));
        $entries[] = [
            'id' => $id,
            'publicKey' => $publicKey,
            'signCount' => $signCount,
            'name' => '' === trim($name) ? 'Passkey' : mb_substr(trim($name), 0, 64),
            'createdAt' => time(),
            'lastUsedAt' => null,
        ];

        $this->save($entries);
    }

    public function markUsed(string $id, int $signCount): void
    {
        $entries = $this->all();
        foreach ($entries as &$entry) {
            if ($entry['id'] === $id) {
                $entry['signCount'] = $signCount;
                $entry['lastUsedAt'] = time();
            }
        }
        unset($entry);

        $this->save($entries);
    }

    public function remove(string $id): bool
    {
        $entries = $this->all();
        $remaining = array_values(array_filter($entries, static fn (array $entry): bool => $entry['id'] !== $id));
        if (count($remaining) === count($entries)) {
            return false;
        }

        $this->save($remaining);

        return true;
    }

    /**
     * @param list<array<string, mixed>> $entries
     */
    private function save(array $entries): void
    {
        $dir = dirname($this->file);
        if (!is_dir($dir)) {
            mkdir($dir, 0o700, true);
        }

        $tmp = $this->file.'.tmp';
        file_put_contents($tmp, json_encode($entries, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
        chmod($tmp, 0o600);
        rename($tmp, $this->file);
    }
}
