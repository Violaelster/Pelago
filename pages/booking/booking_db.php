<?php

require_once __DIR__ . '/../../config/app.php';

declare(strict_types=1);

/**
 * Retrieves information needed for the booking form.
 * - Room details (types, prices, discounts)
 * - Feature details (names, prices)
 * - General settings (e.g., hotel stars and welcome message)
 */
function getBookingData(): array
{
    try {
        // Establish database connection
        $db = getDb();
    } catch (PDOException $e) {
        // Log error and return a JSON error response
        error_log("Database connection error in getBookingData: " . $e->getMessage());
        echo json_encode([
            'status' => 'error',
            'message' => "Unable to connect to the database. Please try again later."
        ]);
        exit;
    }

    // Fetch room data
    $roomQuery = "SELECT id, room_type, price, discount 
                  FROM rooms 
                  ORDER BY price ASC";
    $stmt = $db->prepare($roomQuery);
    $stmt->execute();
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch feature data
    $featureQuery = "SELECT id, feature_name, price 
                     FROM features 
                     ORDER BY price ASC";
    $stmt = $db->prepare($featureQuery);
    $stmt->execute();
    $features = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch general settings
    $settingsQuery = "SELECT setting_name, setting_value 
                      FROM admin_settings 
                      WHERE setting_name IN ('hotel_stars', 'booking_welcome_text')";
    $stmt = $db->prepare($settingsQuery);
    $stmt->execute();
    $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // Return fetched data
    return [
        'rooms' => $rooms,
        'features' => $features,
        'settings' => $settings
    ];
}
