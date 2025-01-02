<?php

declare(strict_types=1);
require_once __DIR__ . '/../../config/app.php';

// Ta bort den gamla connectDatabase funktionen eftersom vi nu använder database.php

/**
 * Handle POST requests to update data in the database.
 *
 * @param PDO $db
 * @return string Feedback for the admin
 */
function handlePostRequest(PDO $db): string
{
    ob_start();

    if (!empty($_POST['room_prices'])) {
        updateRoomPrices($db, $_POST['room_prices']);
        echo "Room prices and discounts updated successfully! ";
    }

    if (!empty($_POST['feature_prices'])) {
        updateFeaturePrices($db, $_POST['feature_prices']);
        echo "Feature prices updated successfully! ";
    }

    if (!empty($_POST['admin_settings'])) {
        updateAdminSettings($db, $_POST['admin_settings']);
        echo "Admin settings updated successfully!";
    }

    return ob_get_clean();
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
 * Fetch room and feature data for rendering the form.
 *
 * @param PDO $db
 * @return array
 */
function fetchDataForForm(PDO $db): array
{
    $stmt = $db->query("SELECT id, room_type, price, discount FROM rooms");
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $db->query("SELECT id, feature_name, price FROM features");
    $features = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $db->query("SELECT setting_name, setting_value FROM admin_settings");
    $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    return compact('rooms', 'features', 'settings');
}

/**
 * Update admin settings.
 *
 * @param PDO $db
 * @param array $settings
 * @return void
 */
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
