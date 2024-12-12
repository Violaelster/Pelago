<?php

declare(strict_types=1);

try {
    // Connect to the database
    $db = new PDO('sqlite:hotel-bookings.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if the form is submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Check and update room prices and discount if present
        if (
            isset($_POST['price_budget'], $_POST['price_standard'], $_POST['price_luxury'], $_POST['discount'])
        ) {
            $stmt = $db->prepare("
                UPDATE admin
                SET price_budget = :price_budget,
                    price_standard = :price_standard,
                    price_luxury = :price_luxury,
                    discount = :discount
                WHERE id = 1
            ");

            $stmt->execute([
                ':price_budget' => $_POST['price_budget'],
                ':price_standard' => $_POST['price_standard'],
                ':price_luxury' => $_POST['price_luxury'],
                ':discount' => $_POST['discount']
            ]);

            echo "Room prices and discount updated successfully!<br>";
        }

        // Check and update feature prices if present
        if (isset($_POST['feature_prices']) && is_array($_POST['feature_prices'])) {
            foreach ($_POST['feature_prices'] as $id => $price) {
                $stmt = $db->prepare("
                    UPDATE features
                    SET price = :price
                    WHERE id = :id
                ");
                $stmt->execute([
                    ':price' => $price,
                    ':id' => $id
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
