<?php

declare(strict_types=1);

require_once __DIR__ . '/admin_panel.php';

$db = connectDatabase();
$feedback = '';
$data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $feedback = handlePostRequest($db);
}

$data = fetchDataForForm($db);

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
    <p><?= htmlspecialchars($feedback) ?></p>
    <form method="POST" action="">
        <h2>Update Room Prices</h2>
        <?php foreach ($data['rooms'] as $room): ?>
            <label><?= htmlspecialchars($room['room_type']) ?> Price:</label>
            <input type="number" name="room_prices[<?= $room['id'] ?>][price]" value="<?= $room['price'] ?>" step="0.01" required><br>
            <label><?= htmlspecialchars($room['room_type']) ?> Discount (%):</label>
            <input type="number" name="room_prices[<?= $room['id'] ?>][discount]" value="<?= $room['discount'] ?>" step="0.01" required><br><br>
        <?php endforeach; ?>
        <h2>Update Feature Prices</h2>
        <?php foreach ($data['features'] as $feature): ?>
            <label><?= htmlspecialchars($feature['feature_name']) ?> Price:</label>
            <input type="number" name="feature_prices[<?= $feature['id'] ?>]" value="<?= $feature['price'] ?>" step="0.01" required><br><br>
        <?php endforeach; ?>
        <button type="submit">Update Prices</button>
    </form>
</body>

</html>