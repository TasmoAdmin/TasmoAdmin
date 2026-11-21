<?php

namespace TasmoAdmin\Health;

class HealthRepository
{
    private const COLUMNS = [
        'device_id', 'state', 'http_up', 'mqtt_up',
        'last_http_ok', 'last_mqtt_ok', 'last_seen',
        'rssi', 'signal', 'updated_at',
        'mqtt_topic', 'mqtt_expected',
    ];

    // Columns added after the first release; created on existing databases.
    private const LATER_COLUMNS = [
        'mqtt_topic' => 'TEXT',
        'mqtt_expected' => 'INTEGER',
    ];

    private ?\PDO $pdo = null;

    public function __construct(private string $dbPath) {}

    public function ensureSchema(): void
    {
        $dir = \dirname($this->dbPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0o755, true);
        }

        $pdo = $this->connect(true);
        $pdo->exec('PRAGMA journal_mode=WAL');
        $pdo->exec('PRAGMA busy_timeout=5000');
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS device_health ('
            .'device_id TEXT PRIMARY KEY,'
            .'state TEXT,'
            .'http_up INTEGER,'
            .'mqtt_up INTEGER,'
            .'last_http_ok INTEGER,'
            .'last_mqtt_ok INTEGER,'
            .'last_seen INTEGER,'
            .'rssi INTEGER,'
            .'signal INTEGER,'
            .'updated_at INTEGER'
            .')'
        );

        $existing = array_column($pdo->query('PRAGMA table_info(device_health)')->fetchAll(\PDO::FETCH_ASSOC), 'name');
        foreach (self::LATER_COLUMNS as $column => $type) {
            if (!in_array($column, $existing, true)) {
                $pdo->exec(sprintf('ALTER TABLE device_health ADD COLUMN %s %s', $column, $type));
            }
        }
    }

    public function upsert(array $row): void
    {
        $pdo = $this->connect(true);
        $pdo->exec('PRAGMA busy_timeout=5000');

        $data = [];
        foreach (self::COLUMNS as $column) {
            $data[$column] = $row[$column] ?? null;
        }

        $columns = implode(',', self::COLUMNS);
        $placeholders = ':'.implode(',:', self::COLUMNS);
        $updates = [];
        foreach (self::COLUMNS as $column) {
            if ('device_id' === $column) {
                continue;
            }
            $updates[] = sprintf('%1$s=excluded.%1$s', $column);
        }

        $sql = sprintf(
            'INSERT INTO device_health (%s) VALUES (%s) '
            .'ON CONFLICT(device_id) DO UPDATE SET %s',
            $columns,
            $placeholders,
            implode(',', $updates)
        );

        $statement = $pdo->prepare($sql);
        foreach ($data as $key => $value) {
            $statement->bindValue(':'.$key, $value);
        }
        $statement->execute();
    }

    public function get(string $deviceId): ?array
    {
        try {
            $pdo = $this->connect(false);
            $statement = $pdo->prepare('SELECT * FROM device_health WHERE device_id = :id');
            $statement->execute([':id' => $deviceId]);
            $row = $statement->fetch(\PDO::FETCH_ASSOC);

            return false === $row ? null : $row;
        } catch (\PDOException) {
            return null;
        }
    }

    public function all(): array
    {
        try {
            $pdo = $this->connect(false);
            $statement = $pdo->query('SELECT * FROM device_health ORDER BY device_id');

            return $statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException) {
            return [];
        }
    }

    private function connect(bool $createIfMissing): \PDO
    {
        if (null !== $this->pdo) {
            return $this->pdo;
        }

        if (!$createIfMissing && !file_exists($this->dbPath)) {
            throw new \PDOException('health db not present');
        }

        $this->pdo = new \PDO('sqlite:'.$this->dbPath);
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        return $this->pdo;
    }
}
