<?php

declare(strict_types=1);
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/admin_panel.php';
include __DIR__ . '/../../components/header.php';

$db = getDb();
$feedback = '';
$data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $feedback = handlePostRequest($db);
}

$data = fetchDataForForm($db);
?>

<main id="admin-panel">
    <div class="feedback-message">
        <?= htmlspecialchars($feedback) ?>
    </div>

    <form method="POST" action="">
        <section class="admin-section">
            <h2>Update Room Prices</h2>
            <div class="price-grid">
                <?php foreach ($data['rooms'] as $room): ?>
                    <div class="price-item">
                        <label><?= htmlspecialchars($room['room_type']) ?> Price:</label>
                        <input type="number" name="room_prices[<?= $room['id'] ?>][price]"
                            value="<?= $room['price'] ?>" step="0.01" required>

                        <label><?= htmlspecialchars($room['room_type']) ?> Discount (%):</label>
                        <input type="number" name="room_prices[<?= $room['id'] ?>][discount]"
                            value="<?= $room['discount'] ?>" step="0.01" required>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="admin-section">
            <h2>Update Feature Prices</h2>
            <div class="price-grid">
                <?php foreach ($data['features'] as $feature): ?>
                    <div class="price-item">
                        <label><?= htmlspecialchars($feature['feature_name']) ?> Price:</label>
                        <input type="number" name="feature_prices[<?= $feature['id'] ?>]"
                            value="<?= $feature['price'] ?>" step="0.01" required>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="admin-section">
            <h2>Update Hotel Settings</h2>
            <label>Hotel Stars (1-5):</label>
            <input type="number" name="admin_settings[hotel_stars]"
                value="<?= $data['settings']['hotel_stars'] ?? 3 ?>"
                min="1" max="5" required>

            <label>Welcome Message:</label>
            <textarea name="admin_settings[booking_welcome_text]" rows="4" required><?=
                                                                                    htmlspecialchars($data['settings']['booking_welcome_text'] ??
                                                                                        'Welcome to Smooth Oasis! Get ready to experience the perfect blend of relaxation and style.')
                                                                                    ?></textarea>

            <button type="submit">Update Settings</button>
        </section>
    </form>
</main>
<?php include __DIR__ . '/../../components/footer.php'; ?>