<?php

/**
 * Footer Component
 *
 * Contains social media icons to the left, a snoop-svg linking to the admin
 * panel in the center and a list of links to the right. A sektion at the bottom
 * containing payment icons, company values and copyright. 
 */
require_once __DIR__ . '/../config/paths.php';
?>
<footer class="footer">
    <div class="footer-container">
        <!-- Social Media Icons -->
        <div class="footer-icons">
            <img src="<?= BASE_PATH ?>/assets/footer-icons/instagram-icon.png" alt="Instagram">
            <img src="<?= BASE_PATH ?>/assets/footer-icons/facebook-icon.png" alt="Facebook">
            <img src="<?= BASE_PATH ?>/assets/footer-icons/chat-icon.png" alt="Chat">
            <img src="<?= BASE_PATH ?>/assets/footer-icons/email-icon.png" alt="Email">
            <img src="<?= BASE_PATH ?>/assets/footer-icons/phone-icon.png" alt="Phone">
        </div>

        <!-- Center Image -->
        <div class="footer-center">
            <a href="<?= BASE_PATH ?>/pages/admin/update_admin.php">
                <img
                    src="<?= BASE_PATH ?>/assets/snoop-icon.svg"
                    alt="Icon to admin panel" />
            </a>
        </div>

        <!-- Links -->
        <ul class="footer-links">
            <h3>Know shmore</h3>
            <li><a href="#">The Snoop Legacy</a></li>
            <li><a href="#">The Smooth Team</a></li>
            <li><a href="#">The Doggfather's Story</a></li>
        </ul>
    </div>

    <!-- Payment Info -->
    <div class="footer-bottom">
        <div class="payment-icons">
            <img src="<?= BASE_PATH ?>/assets/footer-icons/visa-icon.svg" alt="Visa">
            <img src="<?= BASE_PATH ?>/assets/footer-icons/mastercard-icon.svg" alt="MasterCard">
            <img src="<?= BASE_PATH ?>/assets/footer-icons/pride-icon.svg" alt="Pride Flag">
        </div>
        <div class="hotel-stars">
            <?php
            require_once __DIR__ . './../config/app.php';
            try {
                $db = getDb();
                $stmt = $db->query("SELECT setting_value FROM admin_settings WHERE setting_name = 'hotel_stars'");
                $starCount = (int) ($stmt->fetchColumn() ?? 3);

                for ($i = 0; $i < $starCount; $i++):
            ?>
                    <img src="<?= BASE_PATH ?>/assets/footer-icons/star.svg" alt="Hotel Star Rating" class="star-rating">
                <?php
                endfor;
            } catch (PDOException $e) {
                for ($i = 0; $i < 3; $i++):
                ?>
                    <img src="<?= BASE_PATH ?>/assets/footer-icons/star.svg" alt="Hotel Star Rating" class="star-rating">
            <?php
                endfor;
            }
            ?>
        </div>
        <p>&copy; Smooth Mansion</p>
    </div>
</footer>
</body>

</html>