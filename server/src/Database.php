<?php

class Database
{
    private PDO $connection;

    public function __construct(
        string $host,
        string $port,
        string $database,
        string $username,
        string $password
    ) {
        $dsn = "pgsql:host=$host;port=$port;dbname=$database";

        $this->connection = new PDO(
            $dsn,
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    }

    public function connection(): PDO
    {
        return $this->connection;
    }
}
