<?php

namespace TasmoAdmin\Backup;

use Generator;

class BackupResults
{
    /**
     * @var BackupResult[]
     */
    private array $results;

    public function __construct(array $results)
    {
        $this->results = $results;
    }
    public function successful(): bool
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

    /**
     * @return BackupResult[]
     */
    public function getFailures(): array
    {
        $failures = [];
        foreach ($this->results as $result) {
            if (!$result->isSuccessful()) {
                $failures[] = $result;
            }
        }

        return $failures;
    }
}
