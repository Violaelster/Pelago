<?php

declare(strict_types=1);

/**
 * Establish a database connection.
 *
 * @return PDO
 */
function connectDatabase(): PDO
{
    try {
        $db = new PDO('sqlite:hotel-bookings.db');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $db;
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}

/**
 * Fetch booking-related data from the database.
 *
 * @return array Associative array containing rooms and features.
 */
function getBookingData(): array
{
    $db = connectDatabase();

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
}

/**
 * Calculate the total cost for a booking.
 *
 * @param array $data
 * @param float $room_price
 * @param float $room_discount
 * @param PDO $db
 * @return float
 */
function calculateTotalCost(array $data, float $room_price, float $room_discount, PDO $db): float
{
    $nights = (strtotime($data['departure_date']) - strtotime($data['arrival_date'])) / (60 * 60 * 24);

    // Room base cost
    $total_cost = $room_price * $nights;

    // Apply discount
    if ($room_discount > 0 && $room_discount <= 100) {
        $discount_amount = $total_cost * ($room_discount / 100);
        $total_cost -= $discount_amount;
    }

    // Add feature costs
    if (!empty($data['features'])) {
        foreach ($data['features'] as $feature_id) {
            $stmt = $db->prepare("SELECT price FROM features WHERE id = :id");
            $stmt->execute([':id' => $feature_id]);
            $feature_price = (float)$stmt->fetchColumn();
            $total_cost += $feature_price;
        }
    }

    return $total_cost;
}

/**
 * Validates if a given string is in UUID format.
 *
 * @param string $uuid
 * @return bool
 */
function isValidUuid(string $uuid): bool
{
    return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $uuid) === 1;
}

/**
 * Validates a transfer code using the central bank API.
 *
 * @param string $transferCode
 * @param float $totalCost
 * @return bool
 */
function validateTransferCodeWithAPI(string $transferCode, float $totalCost): bool
{
    $url = "https://www.yrgopelago.se/centralbank/transferCode";

    $data = [
        'transferCode' => $transferCode,
        'totalCost' => $totalCost,
    ];

    $options = [
        'http' => [
            'header'  => "Content-Type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($data),
        ],
    ];

    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    if ($result === false) {
        return false; // API call failed
    }

    $response = json_decode($result, true);

    return isset($response['valid']) && $response['valid'] === true;
}

// Main booking processing logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = connectDatabase();
    $errors = [];

    // Validate transfer code
    $transfer_code = $_POST['transfer_code'] ?? '';
    if (empty($transfer_code)) {
        $errors[] = "Transfer code is required.";
    } elseif (!isValidUuid($transfer_code)) {
        $errors[] = "Transfer code is not in a valid UUID format.";
    }

    // Validate dates
    $arrival_date = $_POST['arrival_date'] ?? '';
    $departure_date = $_POST['departure_date'] ?? '';
    if (empty($arrival_date) || empty($departure_date)) {
        $errors[] = "Both arrival and departure dates are required.";
    } elseif (strtotime($arrival_date) >= strtotime($departure_date)) {
        $errors[] = "Arrival date must be earlier than departure date.";
    } elseif (!preg_match('/^2025-01-\d{2}$/', $arrival_date) || !preg_match('/^2025-01-\d{2}$/', $departure_date)) {
        $errors[] = "Bookings can only be made for January 2025.";
    }

    // Validate room ID
    $room_id = $_POST['room_id'] ?? '';
    $stmt = $db->prepare("SELECT price, discount FROM rooms WHERE id = :id");
    $stmt->execute([':id' => $room_id]);
    $room = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$room) {
        $errors[] = "Invalid room ID selected.";
    }

    // Check room availability
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
        $errors[] = "The selected room is already booked for the chosen dates.";
    }

    // If no errors, proceed with booking
    if (empty($errors)) {
        $total_cost = calculateTotalCost($_POST, $room['price'], $room['discount'], $db);

        // Validate transfer code with API
        if (!validateTransferCodeWithAPI($transfer_code, $total_cost)) {
            $errors[] = "Transfer code is invalid or has already been used.";
        }
    }

    if (!empty($errors)) {
        echo json_encode(['status' => 'error', 'errors' => $errors]);
        exit;
    }

    // Save booking and features
    try {
        $features = $_POST['features'] ?? [];

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

        $stmt = $db->prepare("
            INSERT INTO bookings_features (booking_id, feature_id)
            VALUES (:booking_id, :feature_id)
        ");
        foreach ($features as $feature_id) {
            $stmt->execute([':booking_id' => $booking_id, ':feature_id' => $feature_id]);
        }

        echo json_encode([
            'status' => 'success',
            'booking_id' => $booking_id,
            'total_cost' => $total_cost,
            'message' => "Booking saved successfully with ID: $booking_id."
        ]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => "Failed to save booking or features."]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => "Invalid request."]);
}
