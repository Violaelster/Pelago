<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book a Room</title>
</head>

<body>
    <h1>Book a Room</h1>
    <form method="POST" action="process_booking.php">
        <!-- Transfer Code -->
        <label for="transfer_code">Transfer Code:</label>
        <input type="text" id="transfer_code" name="transfer_code" required><br><br>

        <!-- Arrival and Departure Dates -->
        <label for="arrival_date">Arrival Date:</label>
        <input type="date" id="arrival_date" name="arrival_date" required><br><br>

        <label for="departure_date">Departure Date:</label>
        <input type="date" id="departure_date" name="departure_date" required><br><br>

        <!-- Room Type -->
        <label for="room_type">Room Type:</label>
        <select id="room_type" name="room_type" required onchange="updateCalendar()">
            <option value="budget">Budget</option>
            <option value="standard">Standard</option>
            <option value="luxury">Luxury</option>
        </select><br><br>

        <!-- Calendar -->
        <h2>Calendar for Selected Room</h2>
        <div id="calendar">
            <!-- The calendar will be displayed here -->
        </div>

        <!-- Additional Features -->
        <fieldset>
            <legend>Additional Features (optional):</legend>
            <?php
            try {
                $db = new PDO('sqlite:hotel-bookings.db');
                $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $stmt = $db->query("SELECT id, feature_name FROM features");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo '
                    <label>
                        <input type="checkbox" name="features[]" value="' . $row['id'] . '">
                        ' . htmlspecialchars($row['feature_name']) . '
                    </label><br>';
                }
            } catch (PDOException $e) {
                echo "Failed to fetch features: " . $e->getMessage();
            }
            ?>
        </fieldset><br>

        <!-- Submit Button -->
        <button type="submit">Submit Booking</button>
    </form>
    <script src="room_calendar.js"></script>
</body>

</html>