<?php

declare(strict_types=1);

class Database
{
    private static ?PDO $connection = null;

    /**
     * Gets a single database connection instance (singleton pattern)
     * 
     * @return PDO Database connection
     * @throws PDOException If connection fails
     */
    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            try {
                // Using __DIR__ to get absolute path relative to this file
                self::$connection = new PDO('sqlite:' . __DIR__ . '/../hotel-bookings.db');
                self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                throw new PDOException("Database connection failed: " . $e->getMessage());
            }
        }
        return self::$connection;
    }

    /**
     * Prevents creating new instances of this class
     */
    private function __construct() {}
}

/**
 * Helper function to get database connection more easily
 * 
 * @return PDO
 */
function getDb(): PDO
{
    return Database::getConnection(3);
}
