<?php
require_once "../db.php";

if (!isset($_GET['id'])) {
    die("No appointment ID received.");
}

$appointment_id = intval($_GET['id']);

// Update status to confirmed
$sql = "UPDATE appointments SET status='confirmed' WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $appointment_id);

if ($stmt->execute()) {
    header("Location: appointments.php?msg=Booking confirmed successfully");
    exit;
} else {
    die("Error confirming booking: " . $conn->error);
}
