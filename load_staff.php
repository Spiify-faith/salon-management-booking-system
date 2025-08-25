<?php
header('Content-Type: application/json');
$mysqli = new mysqli("localhost", "root", "", "salonsync");

$result = $mysqli->query("SELECT id, name, role FROM staff");

$staff = [];
while ($row = $result->fetch_assoc()) {
    $staff[] = [
        "id" => $row['id'],
        "name" => $row['name'],
        "role" => $row['role']
    ];
}

echo json_encode($staff);
?>


