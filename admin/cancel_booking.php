<?php
require_once "../db.php";

if (!isset($_GET['id'])) exit("No appointment ID");
$id = (int)$_GET['id'];

$stmt = $conn->prepare("UPDATE appointments SET status='cancelled' WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

header("Location: appointments.php?msg=Booking Cancelled");
exit;
