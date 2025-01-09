<?php
function depositPayment(string $transferCode, string $arrivalDate, string $departureDate): array
{
    $url = 'https://www.yrgopelago.se/centralbank/deposit';

    // Calculate number of days
    $numberOfDays = (strtotime($departureDate) - strtotime($arrivalDate)) / (60 * 60 * 24);

    $data = [
        'user' => 'Viola',
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
