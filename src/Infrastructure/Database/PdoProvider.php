<?php

namespace HomeWork\Infrastructure\Database;

use PDO;

class PdoProvider
{
    private PDO $pdo;

    public function __construct(
        string $host,
        string $db,
        string $user,
        string $pass,
        string $charset
    ) {
        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $this->pdo = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    public function provide(): PDO {
        return $this->pdo;
    }
}