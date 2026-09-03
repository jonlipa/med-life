<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

final class Database
{
    public static function connect(Config $config): PDO
    {
        if (!in_array('mysql', PDO::getAvailableDrivers(), true)) {
            throw new RuntimeException(
                'MySQL PDO driver is not enabled in the active PHP runtime. '
                . 'Perdor start-med-life.cmd ose medlife-php\\php-runtime.cmd qe ngarkon pdo_mysql automatikisht.'
            );
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $config->get('DB_HOST', '127.0.0.1'),
            $config->get('DB_PORT', '3306'),
            $config->get('DB_NAME', 'medlife'),
            $config->get('DB_CHARSET', 'utf8mb4'),
        );

        try {
            return new PDO(
                $dsn,
                (string) $config->get('DB_USER', 'root'),
                (string) $config->get('DB_PASS', ''),
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ],
            );
        } catch (PDOException $exception) {
            throw new RuntimeException('MySQL connection failed: ' . $exception->getMessage(), 0, $exception);
        }
    }
}
