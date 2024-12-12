<?php

declare(strict_types=1);

try {
    // Connect to the database
    $db = new PDO('sqlite:hotel-bookings.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if the form is submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Collect form data
        $price_budget = $_POST['price_budget'];
        $price_standard = $_POST['price_standard'];
        $price_luxury = $_POST['price_luxury'];
        $discount = $_POST['discount'];

        // Update the admin table with new values
        $stmt = $db->prepare("
         UPDATE admin
         SET price_budget = :price_budget,
             price_standard = :price_standard,
             price_luxury = :price_luxury,
             discount = :discount
         WHERE id = 1
     ");


        $stmt->execute([
            ':price_budget' => $price_budget,
            ':price_standard' => $price_standard,
            ':price_luxury' => $price_luxury,
            ':discount' => $discount
        ]);

        // Success message
        echo "Admin settings updated successfully!";
    } else {
        echo "Invalid request method.";
    }
} catch (PDOException $e) {
    // Error message
    echo "Error: " . $e->getMessage();
}
