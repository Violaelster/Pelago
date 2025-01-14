<?php

declare(strict_types=1);
require_once __DIR__ . '/booking_db.php';
require_once __DIR__ . '/booking_functions.php';



/**
 * Validates if a given string is in UUID format.
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
