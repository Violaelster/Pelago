<?php

require_once __DIR__ . '/booking_db.php';
require_once __DIR__ . '/booking_validation.php';
require_once __DIR__ . '/booking.php';



function depositPayment(string $transferCode, string $arrivalDate, string $departureDate): array
{
    $url = 'https://www.yrgopelago.se/centralbank/deposit';

    // Calculate number of days
    $numberOfDays = (strtotime($departureDate) - strtotime($arrivalDate)) / (60 * 60 * 24);

    $data = [
        'user' => 'Viola', // Replace with your actual username
        'transferCode' => $transferCode,
        'numberOfDays' => $numberOfDays
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
    ]);

    $response = curl_exec($ch);

    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        die("CURL error: $error");
    }

    curl_close($ch);

    $decodedResponse = json_decode($response, true);
    if ($decodedResponse === null) {
        die("Failed to parse API response.");
    }

    return $decodedResponse;
}

// ============================================================================
//  Calculation Functions
// ============================================================================

/**
 * Calculate the total cost for a booking.
 *
 * @param array $data User input data.
 * @param float $room_price Price per night of the room.
 * @param float $room_discount Discount percentage for the room.
 * @param PDO $db Database connection.
 * @return float Total booking cost.
 */
function calculateTotalcost(array $data, float $room_price, float $room_discount, PDO $db): float
{
    // Calculate number of nights
    $nights = (strtotime($data['departure_date']) - strtotime($data['arrival_date'])) / (60 * 60 * 24);

    // Calculate base room cost
    $total_cost = $room_price * $nights;

    // Apply discount if applicable
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

// ============================================================================
//  Main Booking Logic
// ============================================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = getDb();
    } catch (PDOException $e) {
        error_log("Database connection error: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => "Unable to connect to the database."]);
        exit;
    }

    // Validate user input
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
    $total_cost = calculateTotalcost($_POST, $room['price'], $room['discount'], $db);

    // Validate transfer code via API
    $transferValidation = validateTransferCodeWithAPI($_POST['transfer_code'], $total_cost);
    if (!isset($transferValidation['status']) || $transferValidation['status'] !== 'success') {
        echo json_encode(['status' => 'error', 'message' => "Transfer code is invalid or has already been used."]);
        exit;
    }

    // Process booking
    try {
        // Start transaction
        $db->beginTransaction();

        // Insert booking
        $stmt = $db->prepare("
            INSERT INTO bookings (
                transfer_code, room_id, arrival_date, departure_date, total_cost, status
            ) VALUES (
                :transfer_code, :room_id, :arrival_date, :departure_date, :total_cost, :status
            )
        ");
        $stmt->execute([
            ':transfer_code' => $_POST['transfer_code'],
            ':room_id' => $room_id,
            ':arrival_date' => $_POST['arrival_date'],
            ':departure_date' => $_POST['departure_date'],
            ':total_cost' => $total_cost,
            ':status' => 'confirmed',
        ]);

        $booking_id = $db->lastInsertId();

        // Process deposit
        $depositResponse = depositPayment(
            $_POST['transfer_code'],
            $_POST['arrival_date'],
            $_POST['departure_date']
        );

        if (!isset($depositResponse['status']) || $depositResponse['status'] !== 'success') {
            // Rollback transaction if deposit fails
            $db->rollBack();
            echo json_encode(['status' => 'error', 'message' => "Failed to process payment."]);
            exit;
        }

        // Insert features
        $features = $_POST['features'] ?? [];
        $stmt = $db->prepare("INSERT INTO bookings_features (booking_id, feature_id) VALUES (:booking_id, :feature_id)");
        foreach ($features as $feature_id) {
            $stmt->execute([':booking_id' => $booking_id, ':feature_id' => $feature_id]);
        }

        // Commit transaction
        $db->commit();

        // Prepare room-specific content
        $room_specific_content = [
            'Budget' => [
                'messages' => [
                    'Keepin\' it real in our budget-friendly crib! 💸, Straight outta pocket savings, but still stylin\'! Snoop approved Bare Bones Bunk, fo\' shizzle!'
                ],
                'gifs' => [
                    'https://media2.giphy.com/media/83cdjFtt3f9XWNOHAO/giphy.gif'
                ],
                'motto' => 'Relax your mind, let your conscience be free. Welcome!'
            ],
            'Standard' => [
                'messages' => [
                    'Stay in The D-O-Double Suite – a space as smooth as a classic G-Funk track, tailored for your comfort.'
                ],
                'gifs' => [
                    'https://media2.giphy.com/media/xT9KVvCqLmtooJn6GA/giphy.gif'
                ],
                'motto' => 'Smooth living, done just right. Welcome!'
            ],
            'Luxury' => [
                'messages' => [
                    'Welcome to Tha Platinum Palace – ✨, VIP status activated, Snoop approved luxury! 🕴️ Living lavish in The Platinum Palace! 💎'
                ],
                'gifs' => [
                    'https://media1.giphy.com/media/HRRL24tbWOmEPRQihV/giphy.gif'
                ],
                'motto' => 'Stay smooth, stay legendary. Welcome!'
            ]
        ];

        // Get room type specific content
        $room_type = $room['room_type'];
        $room_content = $room_specific_content[$room_type];
        $message = $room_content['messages'][0];
        $gif = $room_content['gifs'][0];

        // Prepare response
        $response = [
            'status' => 'success',
            'hotel' => [
                'name' => 'Smooth Mansion',
                'island' => 'Fo Shizzle Isle',
                'stars' => 4
            ],
            'booking' => [
                'id' => $booking_id,
                'arrival_date' => $_POST['arrival_date'],
                'departure_date' => $_POST['departure_date'],
                'total_cost' => $total_cost,
                'status' => 'confirmed'
            ],
            'room' => [
                'id' => $room_id,
                'type' => $room['room_type'],
                'price_per_night' => $room['price']
            ],
            'features' => [],
            'personal_message' => [
                'message' => $message,
                'motto' => $room_content['motto'],
                'gif_url' => $gif,
                'signature' => 'Stay smooth, stay cool, stay Snoop!'
            ]
        ];

        // Add features to response
        foreach ($features as $feature_id) {
            $stmt = $db->prepare("SELECT feature_name, price FROM features WHERE id = :id");
            $stmt->execute([':id' => $feature_id]);
            $feature = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($feature) {
                $response['features'][] = $feature;
            }
        }

        // Send response
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    } catch (PDOException $e) {
        // Rollback transaction on error
        $db->rollBack();
        error_log("Database error in booking process: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => "Failed to save booking or features."]);
        exit;
    }
}
