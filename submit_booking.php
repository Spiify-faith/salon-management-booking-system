<?php
// submit_booking.php

include 'db.php'; // Make sure this connects to your database

// Collect and sanitize POST data
$staff_id = $_POST['staff_id'] ?? null;
$user_id = $_POST['user_id'] ?? null;
$service_type = $_POST['service_type'] ?? '';
$appointment_date = $_POST['appointment_date'] ?? '';
$notes = $_POST['notes'] ?? '';
$status = 'pending'; // default status

// Basic validation
if (!$staff_id || !$user_id || !$service_type || !$appointment_date) {
    echo "Missing required fields.";
    exit;
}

// Prepare and execute the insert query
$stmt = $conn->prepare("INSERT INTO appointments (staff_id, user_id, service_type, appointment_date, status, notes) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("iissss", $staff_id, $user_id, $service_type, $appointment_date, $status, $notes);

if ($stmt->execute()) {
    echo "<h2>Appointment booked successfully!</h2>";
    echo "<p><a href='booking.html'>Back to calendar</a></p>";
} else {
    echo "<h2>Failed to book appointment: " . $stmt->error . "</h2>";
}

$stmt->close();
$conn->close();
?>

<?php
// save_booking.php

require_once "db.php"; // make sure db connection file is correct

// Receive JSON from DayPilot POST request
$data = json_decode(file_get_contents("php://input"));

// Extract variables
$user_id = intval($data->user_id); // will come from logged-in user session
$staff_id = intval($data->staff_id);
$service_type = trim($data->service_type);
$start_time = date("Y-m-d H:i:s", strtotime($data->start));
$end_time = date("Y-m-d H:i:s", strtotime($data->end));
$notes = isset($data->notes) ? trim($data->notes) : "";
$status = "pending"; // default until admin confirms

// validation
if (!$staff_id || !$user_id || !$service_type || !$appointment_date) {
    echo "Missing required fields.";
    exit;
}

// Insert into DB
$stmt = $conn->prepare("INSERT INTO appointments (user_id, staff_id, service_type, appointment_date, end_time, notes, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("iisssss", $user_id, $staff_id, $service_type, $start_time, $end_time, $notes, $status);

if ($stmt->execute()) {
    echo json_encode(["result" => "OK", "message" => "Booking saved successfully"]);
    echo "<p><a href='booking.html'>Back to calendar</a></p>";
} else {
    echo json_encode(["result" => "ERROR", "message" => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
