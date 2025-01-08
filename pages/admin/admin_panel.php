<?php

declare(strict_types=1);
require_once __DIR__ . '/../../config/app.php';

/**
 * - Processing incoming POST data for updating room prices, feature prices, and admin settings.
 * - Fetching data for rendering the admin panel form (room, feature, and settings data).
 * - Updating the database with the provided admin panel data.
 */

// Process incoming POST requests and provide feedback messages
function handlePostRequest(PDO $db): string
{
    ob_start();

    // Update room prices
    if (!empty($_POST['room_prices'])) {
        updateRoomPrices($db, $_POST['room_prices']);
        echo "Room prices and discounts updated successfully! ";
    }

    // Update feature prices
    if (!empty($_POST['feature_prices'])) {
        updateFeaturePrices($db, $_POST['feature_prices']);
        echo "Feature prices updated successfully! ";
    }

    // Update admin settings
    if (!empty($_POST['admin_settings'])) {
        updateAdminSettings($db, $_POST['admin_settings']);
        echo "Admin settings updated successfully!";
    }

    return ob_get_clean();
}

// Updates room prices and discounts in the database
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

// Updates feature prices in the database
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

// Fetches data for rendering the admin form
function fetchDataForForm(PDO $db): array
{
    // Fetch room data
    $stmt = $db->query("SELECT id, room_type, price, discount FROM rooms");
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch feature data
    $stmt = $db->query("SELECT id, feature_name, price FROM features");
    $features = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch admin settings
    $stmt = $db->query("SELECT setting_name, setting_value FROM admin_settings");
    $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    return compact('rooms', 'features', 'settings');
}

// Updates admin settings in the database
function updateAdminSettings(PDO $db, array $settings): void
{
    $stmt = $db->prepare("
        UPDATE admin_settings
        SET setting_value = :value
        WHERE setting_name = :name
    ");

    foreach ($settings as $name => $value) {
        $stmt->execute([
            ':value' => $value,
            ':name' => $name
        ]);
    }
}
