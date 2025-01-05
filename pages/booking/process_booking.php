<?php

/**
 * Process Booking Handler
 * 
 * This file handles all booking-related operations including:
 * - Data fetching
 * - Input validation
 * - Cost calculations
 * - Payment processing
 * - Booking processing
 */

declare(strict_types=1);
require_once __DIR__ . './../../config/app.php';

// ============================================================================
//  Data Fetching Functions
// ============================================================================

/**
 * Fetch booking-related data from the database.
 * 
 * Returns room and feature information needed for the booking form.
 * Includes room types, prices, discounts and available features.
 *
 * @throws PDOException if database connection fails
 * @return array
 */
function getBookingData(): array
{
    try {
        $db = getDb();
    } catch (PDOException $e) {
        error_log("Database connection error in getBookingData: " . $e->getMessage());
        echo json_encode([
            'status' => 'error',
            'message' => "Unable to connect to the database. Please try again later."
        ]);
        exit;
    }

    // Prepare room query
    $roomQuery = "SELECT id, room_type, price, discount 
                 FROM rooms 
                 ORDER BY price ASC";
    $stmt = $db->prepare($roomQuery);
    $stmt->execute();
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepare features query    
    $featureQuery = "SELECT id, feature_name, price 
                    FROM features 
                    ORDER BY price ASC";
    $stmt = $db->prepare($featureQuery);
    $stmt->execute();
    $features = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get welcome settings
    $settingsQuery = "SELECT setting_name, setting_value 
                     FROM admin_settings 
                     WHERE setting_name IN ('hotel_stars', 'booking_welcome_text')";
    $stmt = $db->prepare($settingsQuery);
    $stmt->execute();
    $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    return [
        'rooms' => $rooms,
        'features' => $features,
        'settings' => $settings
    ];
}

// ============================================================================
//  Validation Functions
// ============================================================================

/**
 * Validates if a given string is in UUID format.
 *
 * @param string $uuid The string to validate.
 * @return bool True if valid UUID, otherwise false.
 */
function isValidUuid(string $uuid): bool
{
    return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $uuid) === 1;
}

/**
 * Validates if a transfer code has the correct UUID format.
 *
 * @param string $transferCode The code to validate format
 * @return bool True if valid UUID format
 */
function validateTransferCodeFormat(string $transferCode): bool
{
    if (empty($transferCode)) {
        return false;
    }
    return isValidUuid($transferCode);
}

/**
 * Validate the transfer code with the central bank API.
 *
 * @param string $transferCode The code to validate
 * @param float $totalCost Total booking cost
 * @return array API response
 */
function validateTransferCodeWithAPI(string $transferCode, float $totalCost): array
{
    $url = 'https://www.yrgopelago.se/centralbank/transferCode';

    $data = [
        'transferCode' => $transferCode,
        'totalcost' => $totalCost
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

/**
 * Validate all user input for a booking request.
 * 
 * Functions
 * - User input
 * - Room availability
 * - Transfer code format
 * - Transfer code usage
 * - Dates
 * - Room ID
 *
 * @param array $data User submitted booking data.
 * @param PDO $db Database connection for room validation.
 * @return array List of validation error messages, empty if validation passed.
 */
function validateInput(array $data, PDO $db): array
{
    $errors = [];

    // Validate transfer code
    $transfer_code = $data['transfer_code'] ?? '';
    if (empty($transfer_code)) {
        $errors[] = "Transfer code is required.";
    } elseif (!validateTransferCodeFormat($transfer_code)) {
        $errors[] = "Transfer code is not in a valid UUID format.";
    } elseif (isTransferCodeUsed($transfer_code, $db)) {
        $errors[] = "Transfer code has already been used.";
    }

    // Validate dates
    $date_errors = validateDates(
        $data['arrival_date'] ?? '',
        $data['departure_date'] ?? ''
    );
    $errors = array_merge($errors, $date_errors);

    // Validate room ID
    $room_id = $data['room_id'] ?? '';
    if (!validateRoomId($room_id, $db)) {
        $errors[] = "Invalid room ID selected.";
    } else {
        // Convert to int for further validation
        $room_id_int = (int)$room_id;
        if (!isRoomAvailable($room_id_int, $data['arrival_date'], $data['departure_date'], $db)) {
            $errors[] = "Room is not available for the selected dates.";
        }
    }

    return $errors;
}

function isRoomAvailable(int $room_id, string $arrival_date, string $departure_date, PDO $db): bool
{
    $stmt = $db->prepare("
        SELECT COUNT(*)
        FROM bookings
        WHERE room_id = :room_id
          AND (
            (arrival_date <= :departure_date AND departure_date >= :arrival_date)
          )
    ");
    $stmt->execute([
        ':room_id' => $room_id,
        ':arrival_date' => $arrival_date,
        ':departure_date' => $departure_date,
    ]);
    return $stmt->fetchColumn() == 0;
}

function isTransferCodeUsed(string $transfer_code, PDO $db): bool
{
    $stmt = $db->prepare("SELECT COUNT(*) FROM bookings WHERE transfer_code = :transfer_code");
    $stmt->execute([':transfer_code' => $transfer_code]);
    return $stmt->fetchColumn() > 0;
}

function validateDates(string $arrival_date, string $departure_date): array
{
    $errors = [];

    if (empty($arrival_date) || empty($departure_date)) {
        $errors[] = "Both arrival and departure dates are required.";
        return $errors;
    }

    if (strtotime($arrival_date) >= strtotime($departure_date)) {
        $errors[] = "Arrival date must be earlier than departure date.";
    }

    if (
        !preg_match('/^2025-01-\d{2}$/', $arrival_date) ||
        !preg_match('/^2025-01-\d{2}$/', $departure_date)
    ) {
        $errors[] = "Bookings can only be made for January 2025.";
    }

    return $errors;
}

function validateRoomId(string $room_id, PDO $db): bool
{
    if (!is_numeric($room_id)) {
        return false;
    }

    $stmt = $db->prepare("SELECT COUNT(*) FROM rooms WHERE id = :id");
    $stmt->execute([':id' => $room_id]);
    return $stmt->fetchColumn() > 0;
}

/**
 * Deposit booking payment to the central bank.
 *
 * @param string $transferCode The validated transfer code
 * @param string $arrivalDate Booking arrival date
 * @param string $departureDate Booking departure date
 * @return array API response
 */
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
