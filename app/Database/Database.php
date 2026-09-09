<?php

declare(strict_types=1);

namespace App\Database;

use PDO;
use PDOException;
use RuntimeException;

/**
 * The single place where a database connection is created.
 *
 * The old project called mysqli_connect() inside every class. Now there is one
 * PDO connection, configured from .env, and every repository receives it.
 */
final class Database
{
    private ?PDO $connection = null;

    /**
     * @param array{host:string, port:int, name:string, user:string, password:string, charset:string} $config
     */
    public function __construct(private readonly array $config)
    {
    }

    public function connection(): PDO
    {
        if ($this->connection instanceof PDO) {
            return $this->connection;
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $this->config['host'],
            $this->config['port'],
            $this->config['name'],
            $this->config['charset']
        );

        try {
            $this->connection = new PDO($dsn, $this->config['user'], $this->config['password'], [
                // Throw exceptions instead of silently returning false.
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                // Return plain associative arrays.
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                // Use real prepared statements (important against SQL injection).
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            // Never leak credentials or the raw driver message to the browser.
            logger('Database connection failed: ' . $e->getMessage());

            throw new RuntimeException('Unable to connect to the database.', 0, $e);
        }

        return $this->connection;
    }
}
