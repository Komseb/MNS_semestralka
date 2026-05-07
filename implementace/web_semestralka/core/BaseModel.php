<?php

namespace App\Core;

use App\Core\Events\Observer;

abstract class BaseModel
{
    protected static array $observers = [];
    //singleton pdo instance
    private static ?\PDO $pdo = null;

    public static function attach(Observer $observer): void {
        self::$observers[] = $observer;
    }

    public static function notify(string $event, array $data): void {
        foreach(self::$observers as $observer) {
            $observer->update($event, $data);
        }
    }
    //get pdo connection, initialize if needed
    protected static function db(): \PDO
    {
        if (self::$pdo === null) {
            //load config
            $config = require __DIR__ . '/../config/database.php';
            $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset=utf8mb4";
            try {
                self::$pdo = new \PDO($dsn, $config['username'], $config['password'], [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (\PDOException $e) {
                //log or handle error
                throw new \RuntimeException("database connection failed: " . $e->getMessage());
            }
        }
        return self::$pdo;
    }

    //helper execute a prepared statement and return statement
    protected static function execute(string $sql, array $params = []): \PDOStatement
    {
        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    //helper fetch all rows
    protected static function fetchAll(string $sql, array $params = []): array
    {
        return self::execute($sql, $params)->fetchAll();
    }

    //helper fetch one row
    protected static function fetchOne(string $sql, array $params = []): ?array
    {
        $row = self::execute($sql, $params)->fetch();
        return $row ?: null;
    }

    //helper get last insert id
    protected static function lastInsertId(): string
    {
        return self::db()->lastInsertId();
    }
}
