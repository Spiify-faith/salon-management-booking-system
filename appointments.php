<?php
session_start();
require_once "../db.php";

// (Optional) restrict access to logged-in admins
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch appointments with related user & staff info
$sql = "
    SELECT a.id, a.service_type, a.appointment_date, a.slot, a.status, a.payment_status,
           u.name AS user_name, u.email AS user_email,
           s.name AS staff_name, s.role AS staff_role
    FROM appointments a
    JOIN users u ON a.user_id = u.id
    JOIN staff s ON a.staff_id = s.id
    ORDER BY a.appointment_date DESC
";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Appointments - SalonSync</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">

  <!-- Navbar -->
  <nav class="bg-white shadow-md py-4 px-6 flex justify-between items-center">
    <h1 class="text-2xl font-bold text-pink-600">SalonSync Admin</h1>
    <a href="dashboard.php" class="text-sm text-pink-500 hover:underline">← Back to Dashboard</a>
  </nav>

  <div class="max-w-6xl mx-auto mt-8 p-6 bg-white rounded-2xl shadow-md">
    <h2 class="text-xl font-semibold text-pink-600 mb-4">Appointments</h2>

    <?php if (isset($_GET['msg'])): ?>
      <div class="mb-4 p-3 rounded bg-green-100 text-green-800">
        ✅ <?php echo htmlspecialchars($_GET['msg']); ?>
      </div>
    <?php endif; ?>

    <table class="w-full border-collapse">
      <thead>
        <tr class="bg-pink-50">
          <th class="border p-2">#</th>
          <th class="border p-2">Customer</th>
          <th class="border p-2">Service</th>
          <th class="border p-2">Staff</th>
          <th class="border p-2">Date</th>
          <th class="border p-2">Slot</th>
          <th class="border p-2">Payment</th>
          <th class="border p-2">Status</th>
          <th class="border p-2">Action</th>
        </tr>
      </thead>
      <tbody>
      <?php while ($row = $result->fetch_assoc()): ?>
        <tr class="text-sm hover:bg-pink-50">
          <td class="border p-2"><?php echo $row['id']; ?></td>
          <td class="border p-2">
            <?php echo htmlspecialchars($row['user_name']); ?><br>
            <span class="text-xs text-gray-500"><?php echo htmlspecialchars($row['user_email']); ?></span>
          </td>
          <td class="border p-2"><?php echo htmlspecialchars($row['service_type']); ?></td>
          <td class="border p-2"><?php echo htmlspecialchars($row['staff_name'] . " (" . $row['staff_role'] . ")"); ?></td>
          <td class="border p-2"><?php echo date("M d, Y H:i", strtotime($row['appointment_date'])); ?></td>
          <td class="border p-2">
            <?php echo ucfirst($row['slot']); ?>
          </td>
          <td class="border p-2">
            <span class="px-2 py-1 rounded text-xs
              <?php echo $row['payment_status']=='paid' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
              <?php echo ucfirst($row['payment_status']); ?>
            </span>
          </td>
          <td class="border p-2">
            <span class="px-2 py-1 rounded text-xs
              <?php
                switch($row['status']) {
                  case 'confirmed': echo 'bg-green-100 text-green-700'; break;
                  case 'pending': echo 'bg-yellow-100 text-yellow-700'; break;
                  case 'pending_payment': echo 'bg-gray-100 text-gray-700'; break;
                  case 'cancelled': echo 'bg-red-100 text-red-700'; break;
                  default: echo 'bg-gray-100 text-gray-700';
                }
              ?>">
              <?php echo ucfirst(str_replace("_"," ",$row['status'])); ?>
            </span>
          </td>
          <td class="border p-2 space-x-2">
            <?php if ($row['payment_status'] == 'paid' && $row['status'] == 'pending'): ?>
              <a href="confirm_booking.php?id=<?php echo $row['id']; ?>" class="bg-green-500 text-white px-3 py-1 rounded text-xs">Confirm</a>
            <?php endif; ?>
            <?php if ($row['status'] == 'confirmed'): ?>
              <a href="cancel_booking.php?id=<?php echo $row['id']; ?>" class="bg-red-500 text-white px-3 py-1 rounded text-xs">Cancel</a>
              <a href="complete_booking.php?id=<?php echo $row['id']; ?>" class="bg-blue-500 text-white px-3 py-1 rounded text-xs">Complete</a>
            <?php endif; ?>
          </td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
