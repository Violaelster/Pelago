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

  <form method="POST" action="process_booking.php">
    <!-- Transfer Code -->
    <label for="transfer_code">Transfer Code:</label>
    <input
      type="text"
      id="transfer_code"
      name="transfer_code"
      required /><br /><br />

    <!-- Arrival and Departure Dates -->
    <label for="arrival_date">Arrival Date:</label>
    <input type="date" id="arrival_date" name="arrival_date" min="2025-01-01" max="2025-01-31" required><br><br>

    <label for="departure_date">Departure Date:</label>
    <input type="date" id="departure_date" name="departure_date" min="2025-01-01" max="2025-01-31" required><br><br>

    <!-- Room Type -->
    <label for="room_type">Room Type:</label>
    <select id="room_type" name="room_id" required>
      <option value="1">Budget</option>
      <option value="2">Standard</option>
      <option value="3">Luxury</option>
    </select><br /><br />

    <!-- Calendar -->
    <h2>Calendar for Selected Room</h2>
    <div id="calendar">
      <!-- The calendar will be displayed here -->
    </div>

    <!-- Additional Features -->
    <fieldset>
      <fieldset>
        <legend>Additional Features (optional):</legend>
        <?php
        $stmt = $db->query("SELECT id, feature_name, price FROM features");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
          echo '
        <label>
            <input type="checkbox" name="features[]" value="' . $row['id'] . '" data-price="' . $row['price'] . '">
            ' . htmlspecialchars($row['feature_name']) . ' ($' . $row['price'] . ')
        </label><br>';
        }
        ?>
      </fieldset>
      <br />

      <label for="room_type">Room Type:</label>
      <select id="room_type" name="room_type" required>
        <?php
        $stmt = $db->query("SELECT id, room_type, price FROM rooms");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
          echo '<option value="' . $row['id'] . '" data-price="' . $row['price'] . '">' . htmlspecialchars($row['room_type']) . ' ($' . $row['price'] . ' per night)</option>';
        }
        ?>
      </select><br><br>

      <?php
      $stmt = $db->query("SELECT discount FROM admin LIMIT 1");
      $discount = (float) $stmt->fetchColumn();
      ?>
      <input type="hidden" id="discount" value="<?php echo $discount; ?>">

      <div id="total_cost">Total Cost: $0.00</div>



      <!-- Submit Button -->
      <button type="submit">Submit Booking</button>
  </form>

  <script src="room_calendar.js"></script>
</body>

</html>