<?php

declare(strict_types=1);

try {
    // Anslut till databasen
    $db = new PDO('sqlite:hotel-bookings.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Lista tabeller i databasen
    $result = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
    foreach ($result as $row) {
        echo "Tabell: " . $row['name'] . "<br>";
    }
} catch (PDOException $e) {
    echo "Fel vid anslutning: " . $e->getMessage();
}
