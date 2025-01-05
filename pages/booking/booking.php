<?php

declare(strict_types=1);
require_once __DIR__ . './../../config/app.php';
include __DIR__ . './../../components/header.php';
require_once __DIR__ . '/process_booking.php';

$data = getBookingData();
?>



<main>
  <div class="popup-overlay">
    <div class="welcome-popup">
      <button class="welcome-popup-close">&times;</button>
      <div class="welcome-popup-content">
        <p><?= htmlspecialchars($data['settings']['booking_welcome_text'] ?? 'Welcome to Smooth Mansion!') ?></p>
      </div>
    </div>
  </div>

  <main id="booking-section">
    <section id="options-section">
      <article id="form-section">
        <h2>Book Room</h2>
        <form method="POST" action="process_booking.php">
          <label for="transfer_code">Transfer Code:</label>
          <input type="text" id="transfer_code" name="transfer_code" required /><br /><br />

          <label for="arrival_date">Arrival Date:</label>
          <input type="date" id="arrival_date" name="arrival_date" min="2025-01-01" max="2025-01-31" required><br><br>

          <label for="departure_date">Departure Date:</label>
          <input type="date" id="departure_date" name="departure_date" min="2025-01-01" max="2025-01-31" required><br><br>

          <label for="room_type">Room Type:</label>
          <select id="room_type" name="room_id" required>
            <?php foreach ($data['rooms'] as $room): ?>
              <option value="<?= $room['id'] ?>" data-price="<?= $room['price'] ?>" data-discount="<?= $room['discount'] ?>">
                <?= htmlspecialchars($room['room_type']) ?>
                ($<?= $room['price'] ?> per night <?= $room['discount'] > 0 ? '- ' . $room['discount'] . '% off after first night!' : '' ?>)
              </option>
            <?php endforeach; ?>
          </select>

          <article id="feature-section">
            <fieldset>
              <legend>Additional Features</legend>
              <?php foreach ($data['features'] as $feature): ?>
                <label>
                  <input type="checkbox" name="features[]" value="<?= $feature['id'] ?>" data-price="<?= $feature['price'] ?>">
                  <?= htmlspecialchars($feature['feature_name']) ?> ($<?= $feature['price'] ?>)
                </label><br>
              <?php endforeach; ?>
            </fieldset>
          </article>

          <div id="submit-section">
            <div id="total_cost">Total Cost: $0.00</div>
            <button type="submit">Submit Booking</button>
          </div>
        </form>
      </article>
    </section>

    <aside>
      <section id="calendar-section">
        <h2>
          Calendar for Selected Room
        </h2>
        <div id="calendar"></div>
      </section>

      <section id="rooms-section">
        <?php foreach ($data['rooms'] as $room): ?>
          <div class="room">
            <img src="./../../assets/images/<?= strtolower($room['room_type']) ?>-room.png" alt="<?= $room['room_type'] ?> Room">
            <div class="room-info">
              <div class="room-details">
                <h2><?= $room['room_type'] ?></h2>
                <p>Size: <?= $room['room_type'] === 'Budget' ? '30m²' : ($room['room_type'] === 'Standard' ? '50m²' : '100m²') ?></p>
                <p>Price: $<?= $room['price'] ?>/night</p>
              </div>

              <div class="room-text">
                <?php if ($room['room_type'] === 'Budget'): ?>
                  <h3>The Bare Bones Bunk</h3>
                  <p>"For the budget baller. Keep it simple, keep it smooth. Tha Bare Bones Bunk is perfect for those who roll with style on a budget."</p>
                <?php elseif ($room['room_type'] === 'Standard'): ?>
                  <h3>Tha D-O-Double Suite</h3>
                  <p>"Tha D-O-Double Suite blends laid-back vibes with just the right amount of flair – the perfect spot for chillin’ in style."</p>
                <?php else: ?>
                  <h3>Tha Platinum Palace</h3>
                  <p>"Elevate your stay at Tha Platinum Palace – where every detail shines and the vibes are nothing but premium."</p>
                <?php endif; ?>
              </div>
              <div class="facilities-details">
                <h3>Facilities</h3>
                <ul>
                  <?php if ($room['room_type'] === 'Budget'): ?>
                    <li>Single bed</li>
                    <li>Private bathroom</li>
                    <li>Basic TV</li>
                    <li>WiFi</li>
                  <?php elseif ($room['room_type'] === 'Standard'): ?>
                    <li>Double bed</li>
                    <li>Private bathroom</li>
                    <li>Smart TV</li>
                    <li>WiFi</li>
                    <li>Mini fridge</li>
                  <?php else: ?>
                    <li>King size bed</li>
                    <li>Luxury bathroom with jacuzzi</li>
                    <li>65" Smart TV</li>
                    <li>High-speed WiFi</li>
                    <li>Mini bar</li>
                    <li>Room service</li>
                  <?php endif; ?>
                </ul>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </section>
    </aside>
  </main>
  <?php include __DIR__ . './../../components/footer.php'; ?>

  <input type="hidden" id="discount" value="<?= $data['rooms'][0]['discount'] ?>">

  <script src="./../../public/js/booking.js"></script>
  <script src="./../../public/js/room_calendar.js"></script>
  </body>