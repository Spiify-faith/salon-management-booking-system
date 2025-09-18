<?php
// enable_errors.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>Error Reporting Enabled</h2>";
echo "<p>PHP errors will now be displayed on screen.</p>";
echo "<p>Try accessing your booking.php page again to see any errors.</p>";

// Also test including your auth helpers
echo "<p>Testing auth_helpers.php inclusion...</p>";
require_once 'auth_helpers.php';
echo "<p>Auth helpers included successfully.</p>";
?>