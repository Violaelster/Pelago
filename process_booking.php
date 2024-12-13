<?php

declare(strict_types=1);

// Functions (same as before)
function isValidUuid(string $uuid): bool
{
    return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $uuid) === 1;
}


// Connect to database
try {
    $db = new PDO('sqlite:hotel-bookings.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}



// Check if form was submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];

    // Validate transfer code
    $transfer_code = $_POST['transfer_code'] ?? '';
    if (empty($transfer_code)) {
        $errors[] = "Transfer code is required.";
    } elseif (!isValidUuid($transfer_code)) {
        $errors[] = "Transfer code is not in a valid UUID format.";
    }

    // Validate arrival and departure dates
    $arrival_date = $_POST['arrival_date'] ?? '';
    $departure_date = $_POST['departure_date'] ?? '';
    if (empty($arrival_date) || empty($departure_date)) {
        $errors[] = "Both arrival and departure dates are required.";
    } elseif (strtotime($arrival_date) >= strtotime($departure_date)) {
        $errors[] = "Arrival date must be earlier than departure date.";
    }


    //Validates that the booking is in january
    if (!preg_match('/^2025-01-\d{2}$/', $arrival_date) || !preg_match('/^2025-01-\d{2}$/', $departure_date)) {
        $errors[] = "Bookings can only be made for January 2025.";
    }

    // Validate room ID
    $room_id = $_POST['room_id'] ?? '';
    if (!is_numeric($room_id)) {
        $errors[] = "Invalid room ID.";
    } else {
        $stmt = $db->prepare("SELECT COUNT(*) FROM rooms WHERE id = :id");
        $stmt->execute([':id' => $room_id]);
        if ($stmt->fetchColumn() == 0) {
            $errors[] = "Invalid room ID selected.";
        }
    }

    // Validate features (if any)
    $features = $_POST['features'] ?? [];
    if (!is_array($features)) {
        $errors[] = "Invalid features selected.";
    } else {
        foreach ($features as $feature_id) {
            $stmt = $db->prepare("SELECT COUNT(*) FROM features WHERE id = :id");
            $stmt->execute([':id' => $feature_id]);
            if ($stmt->fetchColumn() == 0) {
                $errors[] = "One or more selected features are invalid.";
                break;
            }
        }
    }

    // If there are validation errors, display them
    if (!empty($errors)) {
        echo "<h1>Validation Errors:</h1><ul>";
        foreach ($errors as $error) {
            echo "<li>" . htmlspecialchars($error) . "</li>";
        }
        echo "</ul>";
        exit;
    }

    // Fetch the discount value from the admin table
    $stmt = $db->query("SELECT discount FROM admin LIMIT 1");
    $discount = (float) $stmt->fetchColumn();

    // Fetch the discount value from the admin table
    $stmt = $db->query("SELECT discount FROM admin LIMIT 1");
    $discount = (float) $stmt->fetchColumn();

    // Debug: Output the discount value
    echo "Discount value: $discount<br>";




    // Calculate total cost
    $stmt = $db->prepare("SELECT price FROM rooms WHERE id = :id");
    $stmt->execute([':id' => $room_id]);
    $room_cost = $stmt->fetchColumn();

    $feature_cost = 0;
    foreach ($features as $feature_id) {
        $stmt = $db->prepare("SELECT price FROM features WHERE id = :id");
        $stmt->execute([':id' => $feature_id]);
        $feature_cost += (float) $stmt->fetchColumn();
    }

    // Calculate the number of nights
    $nights = (strtotime($departure_date) - strtotime($arrival_date)) / (60 * 60 * 24);

    // Apply discount after the first night
    if ($nights > 1) {
        $discount_amount = ($room_cost * ($nights - 1)) * ($discount / 100);
    } else {
        $discount_amount = 0;
    }

    $total_cost = ($room_cost * $nights + $feature_cost) - $discount_amount;

    // Calculate total cost
    $total_cost = ($room_cost * $nights + $feature_cost) - $discount_amount;

    // Debug: Output calculation details
    echo "Nights: $nights<br>";
    echo "Room cost: $room_cost<br>";
    echo "Feature cost: $feature_cost<br>";
    echo "Discount amount: $discount_amount<br>";
    echo "Total cost: $total_cost<br>";



    // Check for overlapping bookings
    $stmt = $db->prepare("
        SELECT COUNT(*) FROM bookings
        WHERE room_id = :room_id
          AND (
              (:arrival_date BETWEEN arrival_date AND departure_date)
              OR (:departure_date BETWEEN arrival_date AND departure_date)
              OR (arrival_date BETWEEN :arrival_date AND :departure_date)
          )
    ");

    $stmt->execute([
        ':room_id' => $room_id,
        ':arrival_date' => $arrival_date,
        ':departure_date' => $departure_date
    ]);

    if ($stmt->fetchColumn() > 0) {
        die("The selected room is already booked for the chosen dates. Please choose different dates or a different room.");
    }

    // Insert booking
    try {
        $stmt = $db->prepare("
            INSERT INTO bookings (transfer_code, room_id, arrival_date, departure_date, total_cost, status)
            VALUES (:transfer_code, :room_id, :arrival_date, :departure_date, :total_cost, :status)
        ");

        $stmt->execute([
            ':transfer_code' => $transfer_code,
            ':room_id' => $room_id,
            ':arrival_date' => $arrival_date,
            ':departure_date' => $departure_date,
            ':total_cost' => $total_cost,
            ':status' => 'confirmed'
        ]);

        $booking_id = $db->lastInsertId();

        // Insert features
        $stmt = $db->prepare("
            INSERT INTO bookings_features (booking_id, feature_id)
            VALUES (:booking_id, :feature_id)
        ");

        foreach ($features as $feature_id) {
            $stmt->execute([
                ':booking_id' => $booking_id,
                ':feature_id' => $feature_id
            ]);
        }

        echo "Booking and features saved successfully with ID: $booking_id.";
    } catch (PDOException $e) {
        die("Failed to save booking or features: " . $e->getMessage());
    }
} else {
    echo "Invalid request.";
}


$response = [
    'status' => 'success',
    'booking_id' => $booking_id,
    'total_cost' => $total_cost,
    'discount_applied' => $discount_amount,
    'message' => "Booking saved successfully with ID: $booking_id."
];

header('Content-Type: application/json');
echo json_encode($response);
exit;
