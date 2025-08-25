<?php
require_once "db.php"; // adjust path if needed

header('Content-Type: application/json');

$sql = "SELECT a.id, a.service_type, a.appointment_date, s.name AS staff_name
        FROM appointments a
        JOIN staff s ON a.staff_id = s.id
        WHERE a.status != 'cancelled'";

$result = $conn->query($sql);

$events = [];
while ($row = $result->fetch_assoc()) {
    $events[] = [
        'id'    => $row['id'],
        'title' => $row['service_type'] . " - " . $row['staff_name'],
        'start' => $row['appointment_date']
    ];
}

echo json_encode($events);
?>



