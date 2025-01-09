<?php

// app.php serves as the central initialization file for the application.
// This setup makes it easier to manage and scale the project as it grows.
require_once __DIR__ . '/../includes/database.php';

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();
