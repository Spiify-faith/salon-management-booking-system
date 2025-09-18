<?php
require_once "../db.php";

$events = [];

// Fetch confirmed appointments
$sql = "SELECT appointment_date, slot FROM appointments WHERE status='confirmed'";
$result = $conn->query($sql);

$booked_slots = [];

while ($row = $result->fetch_assoc()) {
    $day = date("Y-m-d", strtotime($row['appointment_date']));
    $slot = $row['slot'];

    $booked_slots[$day][] = $slot;
}

// Now create events for calendar
foreach ($booked_slots as $day => $slots) {
    if (in_array("morning", $slots)) {
        $events[] = [
            "title" => "Morning Booked",
            "start" => $day . "T08:00:00",
            "end"   => $day . "T13:00:00",
            "color" => "orange"
        ];
    }

    if (in_array("afternoon", $slots)) {
        $events[] = [
            "title" => "Afternoon Booked",
            "start" => $day . "T13:30:00",
            "end"   => $day . "T17:30:00",
            "color" => "pink"
        ];
    }

    // If both slots taken → block whole day
    if (in_array("morning", $slots) && in_array("afternoon", $slots)) {
        $events[] = [
            "title" => "Fully Booked",
            "start" => $day,
            "end"   => $day,
            "display" => "background",
            "color" => "red"
        ];
    }
}

echo json_encode($events);



