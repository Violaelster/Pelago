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
        // Check that all selected features exist in the database
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

    // If no errors, proceed to save the booking (next step)
    echo "The form has been validated! The next step is to save the booking.";
} else {
    echo "Invalid request.";
}
