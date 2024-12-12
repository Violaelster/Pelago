<?php

declare(strict_types=1);

// Function to validate if a string is in UUID format
function isValidUuid(string $uuid): bool
{
    if (!is_string($uuid) || (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $uuid) !== 1)) {
        return false;
    }
    return true;
}

// Function to make a POST request using file_get_contents()
function makeApiRequest(string $url, array $data): array
{
    $options = [
        'http' => [
            'header' => "Content-Type: application/json\r\n",
            'method' => 'POST',
            'content' => json_encode($data),
            'timeout' => 10 // Optional timeout
        ]
    ];

    $context = stream_context_create($options);
    $response = file_get_contents($url, false, $context);

    if ($response === false) {
        die("API request failed.");
    }

    $httpCode = $http_response_header[0] ?? '';
    if (strpos($httpCode, '200') === false) {
        die("API returned an error: $httpCode - $response");
    }

    return json_decode($response, true);
}

// Validate transfer code with the API
function validateTransferCode(string $transferCode, float $totalCost): bool
{
    $url = "https://www.yrgopelago.se/centralbank/transferCode";
    $data = [
        'transferCode' => $transferCode,
        'totalcost' => $totalCost
    ];

    $response = makeApiRequest($url, $data);
    return isset($response['status']) && $response['status'] === 'success';
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

    // Validate room type
    $room_type = $_POST['room_type'] ?? '';
    $valid_room_types = ['budget', 'standard', 'luxury'];
    if (!in_array($room_type, $valid_room_types, true)) {
        $errors[] = "Invalid room type selected.";
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

    // If there are errors, display them
    if (!empty($errors)) {
        echo "<h1>Validation Errors:</h1><ul>";
        foreach ($errors as $error) {
            echo "<li>" . htmlspecialchars($error) . "</li>";
        }
        echo "</ul>";
        exit;
    }

    // Calculate total cost
    $room_costs = [
        'budget' => 50.00,
        'standard' => 100.00,
        'luxury' => 150.00
    ];
    $room_cost = $room_costs[$room_type];
    $feature_cost = 0;

    foreach ($features as $feature_id) {
        $stmt = $db->prepare("SELECT price FROM features WHERE id = :id");
        $stmt->execute([':id' => $feature_id]);
        $feature_cost += (float) $stmt->fetchColumn();
    }

    $total_cost = $room_cost + $feature_cost;

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
        ':room_id' => array_search($room_type, $valid_room_types) + 1, // Map room type to room_id
        ':arrival_date' => $arrival_date,
        ':departure_date' => $departure_date
    ]);

    if ($stmt->fetchColumn() > 0) {
        die("The selected room is already booked for the chosen dates. Please choose different dates or a different room.");
    }

    // Insert booking into the bookings table
    try {
        $stmt = $db->prepare("
            INSERT INTO bookings (transfer_code, room_id, arrival_date, departure_date, total_cost, status)
            VALUES (:transfer_code, :room_id, :arrival_date, :departure_date, :total_cost, :status)
        ");

        $stmt->execute([
            ':transfer_code' => $transfer_code,
            ':room_id' => array_search($room_type, $valid_room_types) + 1, // Map room type to room_id
            ':arrival_date' => $arrival_date,
            ':departure_date' => $departure_date,
            ':total_cost' => $total_cost,
            ':status' => 'confirmed'
        ]);

        $booking_id = $db->lastInsertId();

        // Insert selected features into the bookings_features table
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
