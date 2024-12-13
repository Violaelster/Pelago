<?php

declare(strict_types=1);

try {
    // Connect to the database
    $db = new PDO('sqlite:hotel-bookings.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Handle POST requests
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Debugging (optional)
        // error_log(print_r($_POST, true));

        if (!empty($_POST['room_prices'])) {
            updateRoomPrices($db, $_POST['room_prices']);
            echo "Room prices and discounts updated successfully!<br>";
        }

        if (!empty($_POST['feature_prices'])) {
            updateFeaturePrices($db, $_POST['feature_prices']);
            echo "Feature prices updated successfully!<br>";
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        renderForm($db);
    } else {
        echo "Invalid request method.";
    }
} catch (PDOException $e) {
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

/**
 * Render the admin panel form.
 *
 * @param PDO $db
 * @return void
 */
function renderForm(PDO $db): void
{
?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Panel</title>
    </head>

    <body>
        <h1>Update Admin Settings</h1>
        <form method="POST" action="">
            <h2>Update Room Prices</h2>
            <?php
            $stmt = $db->query("SELECT id, room_type, price, discount FROM rooms");
            $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($rooms as $room) {
                echo '
                    <label>' . htmlspecialchars($room['room_type']) . ' Price:</label>
                    <input type="number" name="room_prices[' . $room['id'] . '][price]" value="' . $room['price'] . '" step="0.01" required><br>
                    <label>' . htmlspecialchars($room['room_type']) . ' Discount (%):</label>
                    <input type="number" name="room_prices[' . $room['id'] . '][discount]" value="' . $room['discount'] . '" step="0.01" required><br><br>
                ';
            }
            ?>
            <h2>Update Feature Prices</h2>
            <?php
            $stmt = $db->query("SELECT id, feature_name, price FROM features");
            $features = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($features as $feature) {
                echo '
                    <label>' . htmlspecialchars($feature['feature_name']) . ' Price:</label>
                    <input type="number" name="feature_prices[' . $feature['id'] . ']" value="' . $feature['price'] . '" step="0.01" required><br><br>
                ';
            }
            ?>
            <button type="submit">Update Prices</button>
        </form>
    </body>

    </html>
<?php
}
