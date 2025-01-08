<?php

declare(strict_types=1);

/**
 * This file provides a singleton class `Database` to manage a single SQLite database connection.
 * It ensures only one instance of the connection is used throughout the application,
 * preventing unnecessary overhead and maintaining consistency.
 * 
 * Additionally, a helper function `getDb()` is included for easier access to the database connection.
 */


class Database
{
    private static ?PDO $connection = null;

    /**
     * Singleton class for managing a single SQLite database connection.
     * Ensures only one connection instance is used throughout the application.
     */

    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            try {
                self::$connection = new PDO('sqlite:' . __DIR__ . '/../hotel-bookings.db');
                self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                error_log("Database connection failed: " . $e->getMessage());
                throw new PDOException("Database connection failed. Please try again later.");
            }
        }
        return self::$connection;
    }

    private function __construct() {}     // Prevents creating new instances of this class
}
function getDb(): PDO
{
    return Database::getConnection();
}
