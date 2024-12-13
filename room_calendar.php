<?php

declare(strict_types=1);

try {
    // Connect to the database
    $db = new PDO('sqlite:hotel-bookings.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get room type from query parameters (e.g., ?room_type=budget)
    $room_type = $_GET['room_type'] ?? 'budget';

    // Fetch bookings for the specified room type
    $stmt = $db->prepare("
        SELECT b.arrival_date, b.departure_date
        FROM bookings b
        JOIN rooms r ON b.room_id = r.id
        WHERE r.room_type = :room_type
    ");
    $stmt->execute([':room_type' => $room_type]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Generate a list of booked dates
$booked_dates = [];
foreach ($bookings as $booking) {
    $start = new DateTime($booking['arrival_date']);
    $end = new DateTime($booking['departure_date']);
    while ($start <= $end) {
        $booked_dates[] = $start->format('Y-m-d');
        $start->modify('+1 day');
    }
}

// Function to render a simple calendar
function renderCalendar($year, $month, $booked_dates)
{
    $first_day = new DateTime("$year-$month-01");
    $last_day = (clone $first_day)->modify('last day of this month');
    $start_day_of_week = (int) $first_day->format('w'); // 0 (Sunday) to 6 (Saturday)

    echo "<table border='1' style='border-collapse: collapse; width: 100%; text-align: center;'>";
    echo "<caption style='font-size: 1.5em; margin-bottom: 10px;'>" . $first_day->format('F Y') . "</caption>";
    echo "<tr><th>Sun</th><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th></tr><tr>";

    // Blank cells before the first day
    for ($i = 0; $i < $start_day_of_week; $i++) {
        echo "<td></td>";
    }

    $current_date = clone $first_day;

    while ($current_date <= $last_day) {
        $date_str = $current_date->format('Y-m-d');
        if ($current_date->format('w') == 0 && $current_date != $first_day) {
            echo "</tr><tr>"; // Start a new row for each week
        }

        // Highlight booked dates
        if (in_array($date_str, $booked_dates)) {
            echo "<td style='background-color: red; color: white;'>" . $current_date->format('j') . "</td>";
        } else {
            echo "<td>" . $current_date->format('j') . "</td>";
        }

        $current_date->modify('+1 day');
    }

    // Blank cells after the last day
    $end_day_of_week = (int) $last_day->format('w');
    for ($i = $end_day_of_week; $i < 6; $i++) {
        echo "<td></td>";
    }

    echo "</tr>";
    echo "</table>";
}

// Get current year and month from query parameters or use current date
$year = $_GET['year'] ?? date('Y');
$month = $_GET['month'] ?? date('m');

// Render the calendar
renderCalendar($year, $month, $booked_dates);
