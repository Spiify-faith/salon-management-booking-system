<?php
$host = 'localhost';
$port = 3306; // Default port 
$db = 'salonsync'; //database name
$user = 'root';     // Your MySQL username
$pass = 'seliphil2002'; //password 

// Create connection
$conn = new mysqli($host, $user, $pass, $db, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// echo "Connected successfully"; //  
?>
