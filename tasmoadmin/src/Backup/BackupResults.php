<?php

namespace TasmoAdmin\Backup;

class BackupResults
{
    private string $zipPath;

    /**
     * @var BackupResult[]
     */
    private array $results;

    public function __construct(string $zipPath, array $results)
    {
        $this->zipPath = $zipPath;
        $this->results = $results;
    }

    public function getZipPath(): string
    {
        return $this->zipPath;
    }

    public function getResults(): array
    {
        return $this->results;
    }

    private function hasFailure(): bool
    {
        $successful = true;
        foreach ($this->results as $result) {
            if (!$result->isSuccessful()) {
                $successful = false;
                break;
            }
        }

        return $successful;
    }
}
