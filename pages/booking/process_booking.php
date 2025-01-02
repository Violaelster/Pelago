<?php

/**
 * Process Booking Handler
 * 
 * This file handles all booking-related operations including:
 * - Data fetching
 * - Input validation
 * - Cost calculations
 * - Booking processing
 */

declare(strict_types=1);
require_once __DIR__ . '/../../config/app.php';

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
 * Validate all user input for a booking request.
 * 
 * Functions
 * - User input
 * - Room availity
 * - If transfercode is used
 * - If transfercode is used
 * - Transfer code (UUID format)
 * - Dates,
 * - Room ID (must exist in database)
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
    } elseif (!isValidUuid($transfer_code)) {
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

function validateTransferCode(string $transfer_code): bool
{
    if (empty($transfer_code)) {
        return false;
    }
    return isValidUuid($transfer_code);
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
 * Validate the transfer code using the central bank API.
 *
 * @param string $transferCode The code to validate.
 * @param float $totalCost Total booking cost.
 * @return bool True if transfer code is valid, otherwise false.
 */
function validateTransferCodeWithAPI(string $transferCode, float $totalCost): bool
{
    $url = "https://www.yrgopelago.se/centralbank/transferCode"; // API endpoint.

    // Prepare data payload for API request.
    $data = [
        'transferCode' => $transferCode,
        'totalcost' => $totalCost // API requires lowercase 'cost'.
    ];

    // HTTP options for API request.
    $options = [
        'http' => [
            'header'  => "Content-Type: application/json\r\n", // Set JSON content type.
            'method'  => 'POST', // Use POST method.
            'content' => json_encode($data), // JSON encode the data.
            'ignore_errors' => true, // Ignore HTTP errors.
        ],
    ];

    try {
        $context = stream_context_create($options); // Create stream context.
        $result = file_get_contents($url, false, $context); // Send request.

        if ($result === false) { // Check if request failed.
            error_log('Failed to contact API at $url');
            return false;
        }

        $response = json_decode($result, true); // Decode JSON response.
        if (json_last_error() !== JSON_ERROR_NONE) { // Check for JSON errors.
            error_log("JSON decode error: " . json_last_error_msg()); // Logs the error with an explanation
            return false;
        }

        // Validate response status.
        return isset($response['status']) && $response['status'] === 'success';
    } catch (Exception $e) { // Catch exceptions.
        return false;
    }
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
    // Calculate number of nights.
    $nights = (strtotime($data['departure_date']) - strtotime($data['arrival_date'])) / (60 * 60 * 24);

    // Calculate base room cost.
    $total_cost = $room_price * $nights;

    // Apply discount if applicable.
    if ($room_discount > 0 && $room_discount <= 100) {
        $discount_amount = $total_cost * ($room_discount / 100);
        $total_cost -= $discount_amount;
    }

    // Add feature costs.
    $features = $data['features'] ?? []; // Get selected features.
    foreach ($features as $feature_id) {
        $stmt = $db->prepare("SELECT price FROM features WHERE id = :id"); // Prepare query.
        $stmt->execute([':id' => $feature_id]); // Execute with feature ID.
        $feature_price = (float)$stmt->fetchColumn(); // Get feature price.
        $total_cost += $feature_price; // Add to total cost.
    }

    return $total_cost; // Return total cost.
}

// ============================================================================
//  Main Booking Logic
// ============================================================================

// Main booking processing logic.
if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Check if request is POST.
    try {
        $db = getDb(); // Försök att få databasanslutningen.
    } catch (PDOException $e) {
        error_log("Database connection error: " . $e->getMessage()); // Logga felet.
        echo json_encode(['status' => 'error', 'message' => "Unable to connect to the database."]); // Returnera JSON-fel.
        exit;
    } // Get database connection.

    // Validate user input.
    $errors = validateInput($_POST, $db);
    if (!empty($errors)) { // Check for validation errors.
        echo json_encode(['status' => 'error', 'errors' => $errors]); // Return errors as JSON.
        exit;
    }

    // Fetch room details by ID.
    $room_id = $_POST['room_id'];
    $stmt = $db->prepare("SELECT room_type, price, discount FROM rooms WHERE id = :room_id");
    $stmt->execute([':room_id' => $room_id]); // Execute query with room ID.
    $room = $stmt->fetch(PDO::FETCH_ASSOC); // Fetch room data.

    if (!$room) { // Check if room exists.
        echo json_encode(['status' => 'error', 'message' => "Invalid room ID."]);
        exit;
    }

    // Calculate total cost.
    $total_cost = calculateTotalcost($_POST, $room['price'], $room['discount'], $db);

    // Validate transfer code via API.
    if (!validateTransferCodeWithAPI($_POST['transfer_code'], $total_cost)) {
        echo json_encode(['status' => 'error', 'message' => "Transfer code is invalid or has already been used."]);
        exit;
    }

    // Save booking and features.
    try {
        // Insert booking data into database.
        $stmt = $db->prepare("INSERT INTO bookings (transfer_code, room_id, arrival_date, departure_date, total_cost, status) VALUES (:transfer_code, :room_id, :arrival_date, :departure_date, :total_cost, :status)");
        $stmt->execute([
            ':transfer_code' => $_POST['transfer_code'],
            ':room_id' => $room_id,
            ':arrival_date' => $_POST['arrival_date'],
            ':departure_date' => $_POST['departure_date'],
            ':total_cost' => $total_cost,
            ':status' => 'confirmed',
        ]);

        $booking_id = $db->lastInsertId(); // Get inserted booking ID.

        $features = $_POST['features'] ?? []; // Get selected features.
        $stmt = $db->prepare("INSERT INTO bookings_features (booking_id, feature_id) VALUES (:booking_id, :feature_id)"); // Prepare feature query.
        foreach ($features as $feature_id) {
            $stmt->execute([':booking_id' => $booking_id, ':feature_id' => $feature_id]); // Link features to booking.
        }

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

        // Hämta rumstyp och content
        $room_type = $room['room_type'];
        $room_content = $room_specific_content[$room_type];

        // Hämta meddelande och gif för rumstypen
        $message = $room_content['messages'][0];
        $gif = $room_content['gifs'][0];

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

        foreach ($features as $feature_id) { // Add feature details to response.
            $stmt = $db->prepare("SELECT feature_name, price FROM features WHERE id = :id");
            $stmt->execute([':id' => $feature_id]); // Fetch feature data.
            $feature = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($feature) {
                $response['features'][] = $feature; // Append to response.
            }
        }

        header('Content-Type: application/json'); // Set JSON header.
        echo json_encode($response); // Return response as JSON.
        exit;
    } catch (PDOException $e) { // Catch database exceptions.
        echo json_encode(['status' => 'error', 'message' => "Failed to save booking or features."]);
    }
}
