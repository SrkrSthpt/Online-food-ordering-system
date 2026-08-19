<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use PDOStatement;

/**
 * Thin PDO wrapper (singleton). Prepared statements only.
 */
final class Database
{
    private static ?Database $instance = null;
    private PDO $pdo;

    private function __construct(string $dsn, string $user, string $password, array $options)
    {
        $this->pdo = new PDO($dsn, $user, $password, $options);
    }

    public static function disconnect(): void
    {
        static::$instance = null;
    }

    public static function connect(?string $database = null): self
    {
        if (static::$instance !== null && $database === null) {
            return static::$instance;
        }
        if (static::$instance !== null && $database !== null && static::$instance->databaseName() === $database) {
            return static::$instance;
        }

        $host = config('database.host', '127.0.0.1');
        $port = config('database.port', '3306');
        $name = $database ?? config('database.name', 'foodly');
        $user = config('database.username', 'root');
        $pass = config('database.password', '');
        $charset = config('database.charset', 'utf8mb4');

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $host,
            $port,
            $name,
            $charset
        );

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_STRINGIFY_FETCHES => false,
        ];

        try {
            static::$instance = new self($dsn, $user, $pass, $options);
            static::$instance->pdo()->exec('SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci');
        } catch (PDOException $e) {
            throw new \RuntimeException('Database connection failed: ' . $e->getMessage(), 0, $e);
        }

        return static::$instance;
    }

    public function pdo(): PDO
    {
        return $this->pdo;
    }

    public function databaseName(): string
    {
        return (string)$this->pdo->query('SELECT DATABASE()')->fetchColumn();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function query(string $sql, array $params = []): array
    {
        $stmt = $this->execute($sql, $params);
        return $stmt->fetchAll();
    }

    public function first(string $sql, array $params = []): ?array
    {
        $rows = $this->query($sql . ' LIMIT 1', $params);
        return $rows[0] ?? null;
    }

    public function scalar(string $sql, array $params = []): mixed
    {
        $stmt = $this->execute($sql, $params);
        return $stmt->fetchColumn();
    }

    public function execute(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function lastInsertId(): int
    {
        return (int)$this->pdo->lastInsertId();
    }

    public function transaction(callable $callback): mixed
    {
        $this->pdo->beginTransaction();
        try {
            $result = $callback($this);
            $this->pdo->commit();
            return $result;
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    public function beginTransaction(): void
    {
        $this->pdo->beginTransaction();
    }

    public function commit(): void
    {
        $this->pdo->commit();
    }

    public function rollBack(): void
    {
        $this->pdo->rollBack();
    }

    public function quote(mixed $value): string
    {
        return $this->pdo->quote((string)$value);
    }
}
