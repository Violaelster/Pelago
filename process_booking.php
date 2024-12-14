<?php

// Enforce strict typing
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

    // Fetch rooms
    $rooms = $db->query("SELECT id, room_type, price, discount FROM rooms")->fetchAll(PDO::FETCH_ASSOC);

    // Fetch features
    $features = $db->query("SELECT id, feature_name, price FROM features")->fetchAll(PDO::FETCH_ASSOC);

    return [
        'rooms' => $rooms,
        'features' => $features,
    ];
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
 * Validate user input.
 * This consolidates all validation logic in one place.
 *
 * @param array $data
 * @param PDO $db
 * @return array List of validation errors.
 */
function validateInput(array $data, PDO $db): array
{
    $errors = [];

    // Validate transfer code
    $transfer_code = $data['transfer_code'] ?? '';
    if (empty($transfer_code)) {
        $errors[] = "Transfer code is required.";
    } elseif (!isValidUuid($transfer_code)) {
        $errors[] = "Transfer code is not in a valid UUID format.";
    }

    // Validate dates
    $arrival_date = $data['arrival_date'] ?? '';
    $departure_date = $data['departure_date'] ?? '';
    if (empty($arrival_date) || empty($departure_date)) {
        $errors[] = "Both arrival and departure dates are required.";
    } elseif (strtotime($arrival_date) >= strtotime($departure_date)) {
        $errors[] = "Arrival date must be earlier than departure date.";
    } elseif (!preg_match('/^2025-01-\d{2}$/', $arrival_date) || !preg_match('/^2025-01-\d{2}$/', $departure_date)) {
        $errors[] = "Bookings can only be made for January 2025.";
    }

    // Validate room ID
    $room_id = $data['room_id'] ?? '';
    if (!is_numeric($room_id)) {
        $errors[] = "Invalid room ID.";
    } else {
        $stmt = $db->prepare("SELECT COUNT(*) FROM rooms WHERE id = :id");
        $stmt->execute([':id' => $room_id]);
        if ($stmt->fetchColumn() == 0) {
            $errors[] = "Invalid room ID selected.";
        }
    }

    return $errors;
}

/**
 * Validate the transfer code using the central bank API.
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
        return false;
    }

    $response = json_decode($result, true);
    return isset($response['valid']) && $response['valid'] === true;
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
    $features = $data['features'] ?? [];
    foreach ($features as $feature_id) {
        $stmt = $db->prepare("SELECT price FROM features WHERE id = :id");
        $stmt->execute([':id' => $feature_id]);
        $feature_price = (float)$stmt->fetchColumn();
        $total_cost += $feature_price;
    }

    return $total_cost;
}

// Main booking processing logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = connectDatabase();

    // Validate input
    $errors = validateInput($_POST, $db);
    if (!empty($errors)) {
        echo json_encode(['status' => 'error', 'errors' => $errors]);
        exit;
    }

    // Fetch room details
    $room_id = $_POST['room_id'];
    $stmt = $db->prepare("SELECT room_type, price, discount FROM rooms WHERE id = :room_id");
    $stmt->execute([':room_id' => $room_id]);
    $room = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$room) {
        echo json_encode(['status' => 'error', 'message' => "Invalid room ID."]);
        exit;
    }

    // Calculate total cost
    $total_cost = calculateTotalCost($_POST, $room['price'], $room['discount'], $db);

    // Validate transfer code
    if (!validateTransferCodeWithAPI($_POST['transfer_code'], $total_cost)) {
        echo json_encode(['status' => 'error', 'message' => "Transfer code is invalid or has already been used."]);
        exit;
    }

    // Save booking and features
    try {
        $stmt = $db->prepare("INSERT INTO bookings (transfer_code, room_id, arrival_date, departure_date, total_cost, status) VALUES (:transfer_code, :room_id, :arrival_date, :departure_date, :total_cost, :status)");
        $stmt->execute([
            ':transfer_code' => $_POST['transfer_code'],
            ':room_id' => $room_id,
            ':arrival_date' => $_POST['arrival_date'],
            ':departure_date' => $_POST['departure_date'],
            ':total_cost' => $total_cost,
            ':status' => 'confirmed',
        ]);

        $booking_id = $db->lastInsertId();

        $features = $_POST['features'] ?? [];
        $stmt = $db->prepare("INSERT INTO bookings_features (booking_id, feature_id) VALUES (:booking_id, :feature_id)");
        foreach ($features as $feature_id) {
            $stmt->execute([':booking_id' => $booking_id, ':feature_id' => $feature_id]);
        }

        // Create JSON response
        $response = [
            'status' => 'success',
            'booking_id' => $booking_id,
            'total_cost' => $total_cost,
            'arrival_date' => $_POST['arrival_date'],
            'departure_date' => $_POST['departure_date'],
            'room' => [
                'id' => $room_id,
                'type' => $room['room_type'],
                'price_per_night' => $room['price'],
            ],
            'features' => [],
        ];

        foreach ($features as $feature_id) {
            $stmt = $db->prepare("SELECT feature_name, price FROM features WHERE id = :id");
            $stmt->execute([':id' => $feature_id]);
            $feature = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($feature) {
                $response['features'][] = $feature;
            }
        }

        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => "Failed to save booking or features."]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => "Invalid request."]);
}
