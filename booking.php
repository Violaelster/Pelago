<?php

declare(strict_types=1);

function getBookingData(): array
{
  try {
    $db = new PDO('sqlite:hotel-bookings.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $rooms = [];
    $stmt = $db->query("SELECT id, room_type, price, discount FROM rooms");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $rooms[] = $row;
    }

    $features = [];
    $stmt = $db->query("SELECT id, feature_name, price FROM features");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $features[] = $row;
    }

    return [
      'rooms' => $rooms,
      'features' => $features,
    ];
  } catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
  }
}

$data = getBookingData();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Book a Room</title>
  <link rel="stylesheet" href="styles.css" />
</head>

<body>
  <h1>Book a Room</h1>

  <!-- Booking Form -->
  <form method="POST" action="process_booking.php">
    <!-- Transfer Code -->
    <label for="transfer_code">Transfer Code:</label>
    <input type="text" id="transfer_code" name="transfer_code" required /><br /><br />

    <!-- Arrival and Departure Dates -->
    <label for="arrival_date">Arrival Date:</label>
    <input type="date" id="arrival_date" name="arrival_date" min="2025-01-01" max="2025-01-31" required><br><br>

    <label for="departure_date">Departure Date:</label>
    <input type="date" id="departure_date" name="departure_date" min="2025-01-01" max="2025-01-31" required><br><br>

    <!-- Room Type Dropdown -->
    <label for="room_type">Room Type:</label>
    <select id="room_type" name="room_id" required>
      <?php
      foreach ($data['rooms'] as $room) {
        echo '<option value="' . $room['id'] . '" data-price="' . $room['price'] . '">' .
          htmlspecialchars($room['room_type']) . ' ($' . $room['price'] . ' per night)</option>';
      }
      ?>
    </select><br /><br />

    <!-- Calendar -->
    <h2>Calendar for Selected Room</h2>
    <div id="calendar">
      <!-- The calendar will be dynamically loaded via JavaScript -->
    </div>

    <!-- Additional Features -->
    <fieldset>
      <legend>Additional Features (optional):</legend>
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

    <!-- Discount (hidden input) -->
    <input type="hidden" id="discount" value="<?php echo $data['rooms'][0]['discount']; ?>">

    <!-- Display Total Cost -->
    <div id="total_cost">Total Cost: $0.00</div>

    <!-- Submit Button -->
    <button type="submit">Submit Booking</button>
  </form>

  <!-- JavaScript -->
  <script src="booking.js"></script>
  <script src="room_calendar.js"></script>
</body>

</html>