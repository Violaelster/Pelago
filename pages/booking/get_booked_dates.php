<?php

declare(strict_types=1);
require_once __DIR__ . '/../../includes/database.php';

header('Content-Type: application/json');

try {
    $db = getDb();

    // Get room ID from query parameters
    $room_id = $_GET['room_id'] ?? 0;

    if (!$room_id) {
        echo json_encode(['error' => 'Room ID is required']);
        exit;
    }

    // Fetch booked dates for the given room ID
    $stmt = $db->prepare("
        SELECT arrival_date, departure_date 
        FROM bookings 
        WHERE room_id = :room_id
    ");
    $stmt->execute([':room_id' => $room_id]);
    $booked_dates = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Generate list of all booked dates
    $unavailable_dates = [];
    foreach ($booked_dates as $booking) {
        $start = new DateTime($booking['arrival_date']);
        $end = new DateTime($booking['departure_date']);
        while ($start <= $end) {
            $unavailable_dates[] = $start->format('Y-m-d');
            $start->modify('+1 day');
        }
    }

    // Return the booked dates as JSON
    echo json_encode($unavailable_dates);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error']);
    error_log($e->getMessage());
}
