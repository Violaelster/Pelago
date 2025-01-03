<?php
// Detect if we're on localhost or live server
function getBasePath()
{
    if ($_SERVER['SERVER_NAME'] === 'localhost') {
        return '';  // No prefix on localhost
    } else {
        return '/Pelago';  // Prefix on live server
    }
}

// Define the base path as a constant
define('BASE_PATH', getBasePath());
