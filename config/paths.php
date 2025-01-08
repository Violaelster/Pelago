<?php

/**
 * This file dynamically determines the base path depending on if its
 * running on a local development server (localhost) or a live server (e.g., one.com).
 * 
 * Usage:
 * The `BASE_PATH` constant is defined here and can be used throughout the application 
 * to build absolute paths for assets, links, or includes.
 */
function getBasePath()
{
    // Check the server's hostname to determine if we're on localhost
    if ($_SERVER['SERVER_NAME'] === 'localhost') {
        return '';  // No prefix is needed on localhost
    } else {
        return '/Pelago';  // Use '/Pelago' as the base path on the live server
    }
}

// Define the base path as a constant for global usage in the application
define('BASE_PATH', getBasePath());
