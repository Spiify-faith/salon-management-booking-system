<?php
// booking_form.php
require_once "auth_helpers.php";
// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

require_once "db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id']; // Use logged-in user's ID
    $service_type = $_POST['service_type'];
    $appointment_date = $_POST['appointment_date'];
    $slot = $_POST['slot'];
    $staff_id = $_POST['staff_id'];
    $notes = $_POST['notes'];

    $stmt = $conn->prepare("INSERT INTO appointments (user_id, service_type, appointment_date, slot, staff_id, notes, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
    $stmt->bind_param("isssis", $user_id, $service_type, $appointment_date, $slot, $staff_id, $notes);
    
    if ($stmt->execute()) {
        $appointment_id = $stmt->insert_id;
        header("Location: booking_form.php?success=1&id=" . $appointment_id);
        exit();
    } else {
        $error = "Failed to book appointment: " . $stmt->error;
    }
    $stmt->close();
}

// Get staff for dropdown
$staff = $conn->query("SELECT id, name, role FROM staff WHERE available = 1");

// Get date and slot from URL parameters
$date = isset($_GET['date']) ? $_GET['date'] : '';
$slot = isset($_GET['slot']) ? $_GET['slot'] : '';
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
    
    <?php if (isset($error)): ?>
      <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
      <div class="alert alert-success">
        Appointment booked successfully! Please complete payment to confirm.
      </div>
    <?php endif; ?>
    
    <form method="POST">
      <input type="hidden" name="appointment_date" value="<?php echo $date; ?>">
      <input type="hidden" name="slot" value="<?php echo $slot; ?>">

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
        <label class="form-label">Date & Time</label>
        <input type="text" class="form-control" value="<?php echo $date . ' (' . $slot . ')'; ?>" disabled>
      </div>

      <div class="mb-3">
        <label class="form-label">Notes</label>
        <textarea name="notes" class="form-control"></textarea>
      </div>

      <button type="submit" class="btn btn-pink w-100 mb-3">Confirm Booking</button>
      
      <?php if (isset($_GET['success']) && $_GET['success'] == 1 && isset($_GET['id'])): ?>
        <div class="payment-section mt-4">
          <h4 class="text-center">Complete Payment</h4>
          <form action="/salonsync/zynlepay_charge.php" method="post">
            <input type="hidden" name="appointment_id" value="<?php echo (int)$_GET['id']; ?>">
            <button type="submit" class="btn btn-success w-100">Pay Now (ZynlePay)</button>
          </form>
        </div>
      <?php endif; ?>
    </form>
  </div>
</div>

</body>

<style>
  body {
    background-color: #ffe6f2;
    color: #333;
  }
  .card {
    border-radius: 15px;
    border: none;
    background: #fff;
  }
  h3 {
    color: #e75480;
  }
  .btn-pink, .btn.btn-pink {
    background-color: #e75480;
    color: #fff;
    font-weight: bold;
    border: none;
  }
  .btn-pink:hover {
    background-color: #cc3d68;
    color: #fff;
  }
  .form-control:focus {
    border-color: #e75480;
    box-shadow: 0 0 0 0.2rem rgba(231, 84, 128, 0.25);
  }
  label {
    font-weight: 500;
    color: #e75480;
  }
</style> 
</html>
<?php $conn->close(); ?>

