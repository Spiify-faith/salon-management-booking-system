<?php
require_once "db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = 1; // Replace with logged-in user’s ID (from session)
    $service_type = $_POST['service_type'];
    $appointment_date = $_POST['appointment_date'];
    $staff_id = $_POST['staff_id'];
    $notes = $_POST['notes'];

    $stmt = $conn->prepare("INSERT INTO appointments (user_id, service_type, appointment_date, staff_id, notes, status) VALUES (?, ?, ?, ?, ?, 'pending')");
    $stmt->bind_param("issis", $user_id, $service_type, $appointment_date, $staff_id, $notes);
    $stmt->execute();
    $stmt->close();

    header("Location: booking.html?success=1");
    exit;
}

// Get staff for dropdown
$staff = $conn->query("SELECT id, name, role FROM staff WHERE available = 1");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Book Appointment</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
  <div class="card shadow p-4">
    <h3 class="mb-4 text-center text-pink">Book Appointment</h3>
    <form method="POST">
      <input type="hidden" name="appointment_date" value="<?php echo $_GET['date']; ?>">

      <div class="mb-3">
        <label class="form-label">Service Type</label>
        <input type="text" name="service_type" class="form-control" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Choose Staff</label>
        <select name="staff_id" class="form-control" required>
          <?php while ($row = $staff->fetch_assoc()): ?>
            <option value="<?php echo $row['id']; ?>">
              <?php echo $row['name'] . " (" . $row['role'] . ")"; ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label">Notes</label>
        <textarea name="notes" class="form-control"></textarea>
      </div>

      <button type="submit" class="btn btn-pink w-100">Confirm Booking</button>
    </form>
  </div>
</div>

</body>
</html>

