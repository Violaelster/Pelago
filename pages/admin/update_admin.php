<?php

/**
 * Admin Update Panel
 * 
 * Provides an interface for administrators to manage hotel settings including:
 * - Room prices and discounts
 * - Feature/amenity prices
 * - General hotel settings (star rating, welcome messages)
 * 
 * Security:
 * - Requires active admin session
 * - Input validation and sanitization
 * - Strict type declarations
 */

declare(strict_types=1);

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/admin_panel.php';

// Start session only if it's not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: admin_login.php'); // Redirect to login if not authenticated
    exit;
}

// Initialize variables for feedback and form data
$db = getDb();
$feedback = '';
$data = [];

// Handle form submissions via POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $feedback = handlePostRequest($db);
}

// Fetch all required data for populating the admin form
$data = fetchDataForForm($db);

include __DIR__ . '/../../components/header.php';
?>
<main id="admin-panel">
    <!-- Display feedback messages (success/error) -->
    <div class="feedback-message">
        <?= htmlspecialchars($feedback) ?>
    </div>

    <!-- Admin Form -->
    <form method="POST" action="<?= $_SERVER['PHP_SELF'] ?>">
        <!-- Room Prices Section -->
        <section class="admin-section">
            <h2>Update Room Prices</h2>
            <div class="price-grid">
                <?php foreach ($data['rooms'] as $room): ?>
                    <div class="price-item">
                        <!-- Input for room price -->
                        <label><?= htmlspecialchars($room['room_type']) ?> Price:</label>
                        <input type="number" name="room_prices[<?= $room['id'] ?>][price]"
                            value="<?= $room['price'] ?>" step="0.01" required>
                        <!-- Input for room discount -->
                        <label><?= htmlspecialchars($room['room_type']) ?> Discount (%):</label>
                        <input type="number" name="room_prices[<?= $room['id'] ?>][discount]"
                            value="<?= $room['discount'] ?>" step="0.01" required>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Feature Prices Section -->
        <section class="admin-section">
            <h2>Update Feature Prices</h2>
            <div class="price-grid">
                <?php foreach ($data['features'] as $feature): ?>
                    <div class="price-item">
                        <!-- Input for feature price -->
                        <label><?= htmlspecialchars($feature['feature_name']) ?> Price:</label>
                        <input type="number" name="feature_prices[<?= $feature['id'] ?>]"
                            value="<?= $feature['price'] ?>" step="0.01" required>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- General Hotel Settings Section -->
        <section class="admin-section">
            <h2>Update Hotel Settings</h2>
            <!-- Input for hotel star rating -->
            <label>Hotel Stars (1-5):</label>
            <input type="number" name="admin_settings[hotel_stars]"
                value="<?= $data['settings']['hotel_stars'] ?? 3 ?>"
                min="1" max="5" required>

            <!-- Input for booking welcome message -->
            <label>Welcome Message:</label>
            <textarea name="admin_settings[booking_welcome_text]" rows="4" required><?=
                                                                                    htmlspecialchars($data['settings']['booking_welcome_text'] ?? '')
                                                                                    ?></textarea>
            <!-- Submit button -->
            <button type="submit">Update Settings</button>
        </section>
    </form>
</main>
<script src="<?= BASE_PATH ?>/public/js/admin.js"></script>
<?php include __DIR__ . '/../../components/footer.php'; ?>