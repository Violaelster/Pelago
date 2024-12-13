<?php

declare(strict_types=1);

// Connect to the database
try {
    $db = new PDO('sqlite:hotel-bookings.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(json_encode(['error' => 'Database connection failed']));
}

// Get room ID from query parameters
$room_id = $_GET['room_id'] ?? null;

// Validate room ID
if (!$room_id || !is_numeric($room_id)) {
    die(json_encode(['error' => 'Invalid room ID']));
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
