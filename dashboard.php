<?php
session_start();
if (!isset($_SESSION["admin_id"])) {
  header("Location: login.php");
  exit;
}

$adminName = $_SESSION["admin_name"];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard - SalonSync</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
  <header class="bg-pink-500 text-white py-4 shadow-md">
    <div class="max-w-6xl mx-auto px-4 flex justify-between items-center">
      <h1 class="text-xl font-bold">SalonSync Admin</h1>
      <div>
        <span class="mr-4">Welcome, <?= htmlspecialchars($adminName) ?></span>
        <a href="logout.php" class="bg-white text-pink-500 px-3 py-1 rounded hover:bg-pink-100 transition">Logout</a>
      </div>
    </div>
  </header>

  <main class="max-w-6xl mx-auto px-4 py-10">
    <h2 class="text-2xl font-bold mb-6 text-gray-700">Dashboard</h2>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
      <a href="manage_products.php" class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition text-center">
        <h3 class="text-lg font-semibold text-pink-500">Manage Products</h3>
        <p class="text-sm text-gray-500 mt-2">Add, update or remove inventory.</p>
      </a>

      <a href="manage_staff.php" class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition text-center">
        <h3 class="text-lg font-semibold text-pink-500">Manage Staff</h3>
        <p class="text-sm text-gray-500 mt-2">Assign roles & working hours.</p>
      </a>
       
       <a href="confirm_booking.php" class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition text-center">
        <h3 class="text-lg font-semibold text-pink-500">Confirm Bookings</h3>
        <p class="text-sm text-gray-500 mt-2">view and approve bookings.</p>
      </a>

      <a href="view_bookings.php" class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition text-center">
        <h3 class="text-lg font-semibold text-pink-500">View Bookings</h3>
        <p class="text-sm text-gray-500 mt-2">See and manage appointments.</p>
      </a>

      <a href="issue_receipts.php" class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition text-center">
        <h3 class="text-lg font-semibold text-pink-500">Issue Receipts</h3>
        <p class="text-sm text-gray-500 mt-2">Send digital receipts to clients.</p>
      </a>
    </div>
  </main>
</body>
</html>
