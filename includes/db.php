<?php

function getDatabaseConnection(): PDO
{
    $dbHost = getenv('DB_HOST') ?: 'localhost';
    $dbName = getenv('DB_NAME');
    $dbUser = getenv('DB_USER');
    $dbPassword = getenv('DB_PASSWORD');

    if (!$dbName || !$dbUser || !$dbPassword) {
        throw new RuntimeException('Database configuration is incomplete.');
    }

    try {
        return new PDO(
            "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
            $dbUser,
            $dbPassword,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    } catch (PDOException $error) {
        error_log('Database connection failed: ' . $error->getMessage());

        throw new RuntimeException(
            'Database connection failed. Please check your database settings.'
        );
    }
}