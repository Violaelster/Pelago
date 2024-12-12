<?php

declare(strict_types=1);

// Include Composer's autoload file to load Dotenv and other dependencies
require_once 'vendor/autoload.php';

// Load environment variables from the .env file in the current directory
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Start a session to track if the user is authenticated
session_start();

// Check if the request is a POST request and contains an 'api_key'
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['api_key'])) {
    $input_key = $_POST['api_key']; // Get the API key submitted by the user
    $stored_key = $_ENV['API_KEY']; // Retrieve the API key from the .env file

    // Compare the user's API key with the stored key
    if ($input_key === $stored_key) {
        // If the keys match, set a session variable to mark the user as authenticated
        $_SESSION['authenticated'] = true;

        // Redirect the user to the admin panel
        header("Location: admin_panel.php");
        exit;
    } else {
        // If the keys do not match, display an error message
        echo "Invalid API key!";
    }
}

// If the user is not authenticated, show the API key input form and stop page execution
if (!isset($_SESSION['authenticated']) || !$_SESSION['authenticated']) {
    // Display a simple HTML form for the user to enter the API key
    echo '
       <form method="POST">
           <label for="api_key">Enter API Key:</label>
           <input type="password" name="api_key" id="api_key" required>
           <button type="submit">Submit</button>
       </form>
   ';
    exit; // Stop further execution to ensure the rest of the page doesn't load
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
</head>

<body>
    <h1>Uppdatera admininställningar</h1>

    <!-- Form to update admin settings -->
    <form method="POST" action="update_admin.php">
        <label for="price_budget">Pris för budget:</label>
        <input type="number" name="price_budget" id="price_budget" step "0.01" required><br><br>

        <label for="price_standard">Pris för standard:</label>
        <input type="number" name="price_standard" id="price_standard" step "0.01" required><br><br>

        <label for="price_luxury">Pris för luxury:</label>
        <input type="number" name="price_luxury" id="price_luxury" step "0.01" required><br><br>

        <label for="discount">Pris för rabatt:</label>
        <input type="number" name="discount" id="discount" step "0.01" required><br><br>

        <button type="submit">Uppdatera</button>
</body>

</html>