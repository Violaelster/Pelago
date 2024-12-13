<?php

declare(strict_types=1);

try {
    // Connect to the database
    $db = new PDO('sqlite:hotel-bookings.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if the form is submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Check and update room prices and discounts
        if (isset($_POST['room_prices']) && isset($_POST['room_discounts'])) {
            foreach ($_POST['room_prices'] as $room_id => $price) {
                $discount = $_POST['room_discounts'][$room_id] ?? 0;

                $stmt = $db->prepare("
                    UPDATE rooms
                    SET price = :price, discount = :discount
                    WHERE id = :id
                ");
                $stmt->execute([
                    ':price' => $price,
                    ':discount' => $discount,
                    ':id' => $room_id
                ]);
            }

            echo "Room prices and discounts updated successfully!<br>";
        }

        // Check and update feature prices
        if (isset($_POST['feature_prices']) && is_array($_POST['feature_prices'])) {
            foreach ($_POST['feature_prices'] as $feature_id => $price) {
                $stmt = $db->prepare("
                    UPDATE features
                    SET price = :price
                    WHERE id = :id
                ");
                $stmt->execute([
                    ':price' => $price,
                    ':id' => $feature_id
                ]);
            }

            echo "Feature prices updated successfully!<br>";
        }
    } else {
        echo "Invalid request method.";
    }
} catch (PDOException $e) {
    // Error message
    echo "Error: " . $e->getMessage();
}
