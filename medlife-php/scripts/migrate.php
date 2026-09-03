<?php

declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap/init.php';

$migrationsDir = dirname(__DIR__) . '/database/migrations';
$files = glob($migrationsDir . '/*.sql');

if (empty($files)) {
    fwrite(STDERR, "Asnje skedar migrimi.\n");
    exit(1);
}

sort($files);

$db = app('db');
if (!$db instanceof \PDO || !db_available()) {
    fwrite(STDERR, 'Database unavailable: ' . (db_status()['message'] ?? 'Lidhja me databazen deshtoi.') . PHP_EOL);
    exit(1);
}

ensureMigrationTable($db);
$applied = appliedMigrations($db);
$batch = nextBatch($db);

foreach ($files as $sqlFile) {
    $migration = basename($sqlFile);
    if (isset($applied[$migration])) {
        fwrite(STDOUT, "Duke anashkaluar {$migration} (aplikuar me pare)...\n");
        continue;
    }

    fwrite(STDOUT, "Duke zbatuar {$migration}...\n");

    $sql = file_get_contents($sqlFile);
    if ($sql === false) {
        fwrite(STDERR, "Nuk u lexua {$migration}.\n");
        exit(1);
    }

    try {
        foreach (splitStatements($sql) as $statement) {
            applyStatement($db, $statement);
        }
        markMigrationApplied($db, $migration, $batch);
    } catch (Throwable $exception) {
        fwrite(STDERR, "Migrimi {$migration} deshtoi: {$exception->getMessage()}\n");
        exit(1);
    }
}

fwrite(STDOUT, "Migrimet MySQL u aplikuan me sukses.\n");

function ensureMigrationTable(\PDO $db): void
{
    $db->exec(
        'CREATE TABLE IF NOT EXISTS schema_migrations (
            migration VARCHAR(190) NOT NULL PRIMARY KEY,
            batch INT UNSIGNED NOT NULL,
            applied_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
}

function appliedMigrations(\PDO $db): array
{
    $rows = $db->query('SELECT migration FROM schema_migrations ORDER BY migration ASC')->fetchAll(\PDO::FETCH_COLUMN) ?: [];

    return array_fill_keys($rows, true);
}

function nextBatch(\PDO $db): int
{
    return ((int) $db->query('SELECT COALESCE(MAX(batch), 0) FROM schema_migrations')->fetchColumn()) + 1;
}

function markMigrationApplied(\PDO $db, string $migration, int $batch): void
{
    $statement = $db->prepare('INSERT INTO schema_migrations (migration, batch, applied_at) VALUES (:migration, :batch, NOW())');
    $statement->execute([
        'migration' => $migration,
        'batch' => $batch,
    ]);
}

function splitStatements(string $sql): array
{
    $statements = preg_split('/;\s*(?:\R|$)/', $sql) ?: [];

    return array_values(array_filter(array_map('trim', $statements), static fn (string $statement): bool => $statement !== ''));
}

function applyStatement(\PDO $db, string $statement): void
{
    if (preg_match('/^CREATE\s+INDEX\s+(?:IF\s+NOT\s+EXISTS\s+)?(?P<name>`?[A-Za-z0-9_]+`?)\s+ON\s+(?P<table>`?[A-Za-z0-9_]+`?)\s*\((?P<columns>.+)\)$/is', $statement, $matches)) {
        $indexName = normalizeIdentifier($matches['name']);
        $tableName = normalizeIdentifier($matches['table']);

        if (!indexExists($db, $tableName, $indexName)) {
            $db->exec(sprintf('CREATE INDEX %s ON %s (%s)', $matches['name'], $matches['table'], trim($matches['columns'])));
        }

        return;
    }

    if (preg_match('/^ALTER\s+TABLE\s+(?P<table>`?[A-Za-z0-9_]+`?)\s+ADD\s+COLUMN\s+(?:IF\s+NOT\s+EXISTS\s+)?(?P<column>`?[A-Za-z0-9_]+`?)\s+(?P<definition>.+)$/is', $statement, $matches)) {
        $tableName = normalizeIdentifier($matches['table']);
        $columnName = normalizeIdentifier($matches['column']);

        if (!columnExists($db, $tableName, $columnName)) {
            $db->exec(sprintf('ALTER TABLE %s ADD COLUMN %s %s', $matches['table'], $matches['column'], trim($matches['definition'])));
        }

        return;
    }

    $db->exec($statement);
}

function indexExists(\PDO $db, string $tableName, string $indexName): bool
{
    $statement = $db->prepare(
        'SELECT 1
         FROM information_schema.statistics
         WHERE table_schema = DATABASE() AND table_name = :table_name AND index_name = :index_name
         LIMIT 1'
    );
    $statement->execute([
        'table_name' => $tableName,
        'index_name' => $indexName,
    ]);

    return (bool) $statement->fetchColumn();
}

function columnExists(\PDO $db, string $tableName, string $columnName): bool
{
    $statement = $db->prepare(
        'SELECT 1
         FROM information_schema.columns
         WHERE table_schema = DATABASE() AND table_name = :table_name AND column_name = :column_name
         LIMIT 1'
    );
    $statement->execute([
        'table_name' => $tableName,
        'column_name' => $columnName,
    ]);

    return (bool) $statement->fetchColumn();
}

function normalizeIdentifier(string $identifier): string
{
    return trim($identifier, " \t\n\r\0\x0B`");
}
