<?php

declare(strict_types=1);
require_once __DIR__ . '/../../config/app.php';
include __DIR__ . '/../../components/header.html';
require_once __DIR__ . '/process_booking.php';


$data = getBookingData();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Book a Room</title>
  <link rel="stylesheet" href="/public/css/calendar.css" />
  <link rel="stylesheet" href="/public/css/booking.css" />
</head>

<body>

  <section id="booking-section">

    <article id="options-section">
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
            <?php
            foreach ($data['rooms'] as $room) {
              echo '<option value="' . $room['id'] . '" data-price="' . $room['price'] . '">' .
                htmlspecialchars($room['room_type']) . ' ($' . $room['price'] . ' per night)</option>';
            }
            ?>
          </select><br /><br />
        </form>
      </article>


      <article id="feature-section">

        <fieldset>
          <legend>Additional Features:</legend>
          <?php
          foreach ($data['features'] as $feature) {
            echo '
          <label>
              <input type="checkbox" name="features[]" value="' . $feature['id'] . '" data-price="' . $feature['price'] . '">
              ' . htmlspecialchars($feature['feature_name']) . ' ($' . $feature['price'] . ')
          </label><br>';
          }
          ?>
        </fieldset><br />
      </article>
      <div id="submit-section">

        <div id="total_cost">Total Cost: $0.00</div>

        <button type="submit">Submit Booking</button>
      </div>


    </article>


    <article id="calendar-section">

      <h2>Calendar for Selected Room</h2>
      <div id="calendar">
        <!-- Dynamically loaded via JavaScript -->
      </div>
    </article>

    <article id="rooms-section">
      <h1>Hej</h1>
    </article>

  </section>
  <input type="hidden" id="discount" value="<?php echo $data['rooms'][0]['discount']; ?>">


  <script src="/public/js/booking.js"></script>
  <script src="/public/js/room_calendar.js"></script>

</body>

</html>