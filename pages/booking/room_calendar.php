<?php

declare(strict_types=1);
require_once __DIR__ . '/../../includes/database.php';

// Helper function to send JSON errors
function sendJsonError(string $message): void
{
    echo json_encode(['error' => $message]);
    exit;
}

try {
    $db = getDb();  // Använd den centrala databasanslutningen

    // Get room ID from query parameters
    $room_id = $_GET['room_id'] ?? null;

    // Validate room ID
    if (!$room_id || !is_numeric($room_id)) {
        sendJsonError('Invalid room ID.');
    }

    // Validate that the room ID exists in the database
    $stmt = $db->prepare("SELECT COUNT(*) FROM rooms WHERE id = :room_id");
    $stmt->execute([':room_id' => $room_id]);
    if ($stmt->fetchColumn() === 0) {
        sendJsonError('Room ID does not exist.');
    }

    // Fetch booked dates for the given room ID
    $stmt = $db->prepare("SELECT arrival_date, departure_date FROM bookings WHERE room_id = :room_id");
    $stmt->execute([':room_id' => $room_id]);

    $booked_dates = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $start = new DateTime($row['arrival_date']);
        $end = new DateTime($row['departure_date']);
        while ($start <= $end) {
            $booked_dates[] = $start->format('Y-m-d');
            $start->modify('+1 day');
        }
    }

    // Return the booked dates as JSON
    header('Content-Type: application/json');
    echo json_encode(array_values(array_unique($booked_dates)));
} catch (PDOException $e) {
    sendJsonError('Database error occurred.');
    error_log($e->getMessage());  // Logga det faktiska felet för debugging
}
