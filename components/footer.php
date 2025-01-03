<?php
require_once __DIR__ . '/../config/paths.php';
?>
<footer class="footer">
    <div class="footer-container">
        <!-- Social Media Icons -->
        <div class="footer-icons">
            <img src="<?= BASE_PATH ?>/assets/images/icons/instagram-icon.png" alt="Instagram">
            <img src="<?= BASE_PATH ?>/assets/images/icons/facebook-icon.png" alt="Facebook">
            <img src="<?= BASE_PATH ?>/assets/images/icons/chat-icon.png" alt="Chat">
            <img src="<?= BASE_PATH ?>/assets/images/icons/email-icon.png" alt="Email">
            <img src="<?= BASE_PATH ?>/assets/images/icons/phone-icon.png" alt="Phone">
        </div>

        <!-- Center Image -->
        <div class="footer-center">
            <a href="<?= BASE_PATH ?>/pages/admin/update_admin.php">
                <img
                    src="<?= BASE_PATH ?>/assets/images/snoop-icon.svg"
                    alt="Icon" />
            </a>
        </div>

        <!-- Links -->
        <ul class="footer-links">
            <h3>Know more</h3>
            <li><a href="#">Talk to Tha Smooth Team</a></li>
            <li><a href="#">The Snoop Legacy</a></li>
            <li><a href="#">The Doggfather's Story</a></li>
        </ul>
    </div>

    <!-- Payment Info -->
    <div class="footer-bottom">
        <div class="payment-icons">
            <img src="<?= BASE_PATH ?>/assets/images/icons/visa-icon.svg" alt="Visa">
            <img src="<?= BASE_PATH ?>/assets/images/icons/mastercard-icon.svg" alt="MasterCard">
            <img src="<?= BASE_PATH ?>/assets/images/icons/pride-icon.svg" alt="Pride Flag">
        </div>
        <p>&copy; Smooth Motel</p>
    </div>
</footer>
</body>

</html>