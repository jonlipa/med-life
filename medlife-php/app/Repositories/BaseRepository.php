<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

abstract class BaseRepository
{
    private static ?bool $hasSoftDeletes = null;

    public function __construct(protected PDO $db)
    {
    }

    protected function fetch(string $sql, array $params = []): ?array
    {
        $statement = $this->db->prepare($sql);
        $statement->execute($params);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    protected function fetchAll(string $sql, array $params = []): array
    {
        $statement = $this->db->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    protected function execute(string $sql, array $params = []): bool
    {
        $statement = $this->db->prepare($sql);

        return $statement->execute($params);
    }

    protected function insert(string $sql, array $params = []): int
    {
        $statement = $this->db->prepare($sql);
        $statement->execute($params);

        return (int) $this->db->lastInsertId();
    }

    protected function scalar(string $sql, array $params = []): mixed
    {
        $statement = $this->db->prepare($sql);
        $statement->execute($params);

        return $statement->fetchColumn();
    }

    protected function softDeleteClause(string $alias): string
    {
        if (self::$hasSoftDeletes === null) {
            try {
                $check = $this->db->query("SHOW COLUMNS FROM doctors LIKE 'deleted_at'");
                self::$hasSoftDeletes = ($check && $check->fetch()) ? true : false;
            } catch (\Throwable) {
                self::$hasSoftDeletes = false;
            }
        }

        return self::$hasSoftDeletes ? " AND {$alias}.deleted_at IS NULL " : ' ';
    }
}
