<?php

declare(strict_types=1);

try {
    // Connect to the database
    $db = new PDO('sqlite:hotel-bookings.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Handle POST requests
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Update room prices and discounts
        if (!empty($_POST['room_prices'])) {
            updateRoomPrices($db, $_POST['room_prices']);
            echo "Room prices and discounts updated successfully!<br>";
        }

        // Update feature prices
        if (!empty($_POST['feature_prices'])) {
            updateFeaturePrices($db, $_POST['feature_prices']);
            echo "Feature prices updated successfully!<br>";
        }
    } else {
        echo "Invalid request method.";
    }
} catch (PDOException $e) {
    // Log the error and show a generic message
    error_log("Database error: " . $e->getMessage());
    echo "Error: Could not process the request.";
}

/**
 * Update room prices and discounts.
 *
 * @param PDO $db
 * @param array $roomPrices
 * @return void
 */
function updateRoomPrices(PDO $db, array $roomPrices): void
{
    $stmt = $db->prepare("
        UPDATE rooms
        SET price = :price, discount = :discount
        WHERE id = :id
    ");

    foreach ($roomPrices as $id => $data) {
        $stmt->execute([
            ':price' => $data['price'],
            ':discount' => $data['discount'],
            ':id' => $id
        ]);
    }
}

/**
 * Update feature prices.
 *
 * @param PDO $db
 * @param array $featurePrices
 * @return void
 */
function updateFeaturePrices(PDO $db, array $featurePrices): void
{
    $stmt = $db->prepare("
        UPDATE features
        SET price = :price
        WHERE id = :id
    ");

    foreach ($featurePrices as $id => $price) {
        $stmt->execute([
            ':price' => $price,
            ':id' => $id
        ]);
    }
}
