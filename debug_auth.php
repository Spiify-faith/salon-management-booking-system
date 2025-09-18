<?php
// simple_debug.php

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "Step 1: Starting script<br>";

// Test basic PHP functionality
echo "Step 2: Testing basic PHP<br>";

// Test session functionality
echo "Step 3: Testing sessions<br>";
session_start();
echo "Session ID: " . session_id() . "<br>";

// Test session variables
$_SESSION['test'] = 'Hello World';
echo "Session test value: " . $_SESSION['test'] . "<br>";

echo "Step 4: Testing includes<br>";

// Test including auth_helpers.php
if (file_exists('auth_helpers.php')) {
    echo "auth_helpers.php exists<br>";
    require_once 'auth_helpers.php';
    echo "auth_helpers.php included successfully<br>";
} else {
    echo "ERROR: auth_helpers.php does not exist<br>";
}

echo "Step 5: Testing functions<br>";

// Test isLoggedIn function
if (function_exists('isLoggedIn')) {
    echo "isLoggedIn function exists<br>";
    $loggedIn = isLoggedIn();
    echo "User logged in: " . ($loggedIn ? 'Yes' : 'No') . "<br>";
} else {
    echo "ERROR: isLoggedIn function does not exist<br>";
}

echo "Step 6: Testing redirect<br>";

//