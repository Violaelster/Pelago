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
        <!-- Transfercode -->
        <label for="transfer_code">Transfercode:</label>
        <input type="text" id="transfer_code" name="transfer_code" required><br><br>

        <!-- Arrival and departure -->
        <label for="arrival_date">Ankomstdatum:</label>
        <input type="date" id="arrival_date" name="arrival_date" required><br><br>

        <label for="departure_date">Avresedatum:</label>
        <input type="date" id="departure_date" name="departure_date" required><br><br>

        <!-- Roomtype -->
        <label for="room_type">Rumstyp:</label>
        <select id="room_type" name="room_type" required>
            <option value="budget">Budget</option>
            <option value="standard">Standard</option>
            <option value="luxury">Lyx</option>
        </select><br><br>

        <!-- Feautures -->
        <fieldset>
            <legend>Feautres:</legend>
            <?php
            // Connect to database and fetch available features
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
                echo "Error when fetching feature: " . $e->getMessage();
            }
            ?>
        </fieldset><br>

        <!-- Book -->
        <button type="submit">Book!</button>
    </form>
</body>

</html>