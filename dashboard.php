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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    .sidebar {
      transform: translateX(-100%);
      transition: transform 0.3s ease-in-out;
    }
    .sidebar.open {
      transform: translateX(0);
    }
    .overlay {
      opacity: 0;
      visibility: hidden;
      transition: opacity 0.3s ease-in-out;
    }
    .overlay.open {
      opacity: 0.5;
      visibility: visible;
    }
    .menu-item {
      transition: all 0.2s ease;
    }
    .menu-item:hover {
      background-color: rgba(255, 255, 255, 0.1);
      transform: translateX(5px);
    }
  </style>
</head>
<body class="bg-gray-100 min-h-screen">
  <header class="bg-pink-500 text-white py-4 shadow-md">
    <div class="max-w-6xl mx-auto px-4 flex justify-between items-center">
      <div class="flex items-center">
        <button id="menu-toggle" class="mr-4 text-white focus:outline-none">
          <i class="fas fa-bars text-xl"></i>
        </button>
        <h1 class="text-xl font-bold">SalonSync Admin</h1>
      </div>
      <div>
        <span class="mr-4">Welcome, <?= htmlspecialchars($adminName) ?></span>
        <a href="logout.php" class="bg-white text-pink-500 px-3 py-1 rounded hover:bg-pink-100 transition">Logout</a>
      </div>
    </div>
  </header>

  <!-- Sidebar Overlay -->
  <div id="overlay" class="overlay fixed inset-0 bg-black z-30"></div>

  <!-- Sidebar Menu -->
  <div id="sidebar" class="sidebar fixed top-0 left-0 h-full w-64 bg-pink-700 text-white z-40 shadow-lg">
    <div class="p-5 border-b border-pink-600">
      <h2 class="text-xl font-bold">Admin Menu</h2>
    </div>
    <nav class="mt-6">
      <div class="px-5 py-3 text-pink-200 text-sm font-semibold">MAIN NAVIGATION</div>
      <a href="dashboard.php" class="menu-item flex items-center px-5 py-3 text-white">
        <i class="fas fa-tachometer-alt w-6"></i>
        <span class="ml-3">Dashboard</span>
      </a>
      <a href="manage_products.php" class="menu-item flex items-center px-5 py-3 text-white">
        <i class="fas fa-box-open w-6"></i>
        <span class="ml-3">Manage Products</span>
      </a>
      <a href="manage_staff.php" class="menu-item flex items-center px-5 py-3 text-white">
        <i class="fas fa-users w-6"></i>
        <span class="ml-3">Manage Staff</span>
      </a>
      
      <div class="px-5 py-3 text-pink-200 text-sm font-semibold">BOOKING MANAGEMENT</div>
      <a href="confirm_booking.php" class="menu-item flex items-center px-5 py-3 text-white">
        <i class="fas fa-calendar-check w-6"></i>
        <span class="ml-3">Confirm Bookings</span>
      </a>
      <a href="view_bookings.php" class="menu-item flex items-center px-5 py-3 text-white">
        <i class="fas fa-calendar-alt w-6"></i>
        <span class="ml-3">View Bookings</span>
      </a>
      <a href="appointments.php" class="menu-item flex items-center px-5 py-3 text-white">
        <i class="fas fa-list-alt w-6"></i>
        <span class="ml-3">All Appointments</span>
      </a>
      
      <div class="px-5 py-3 text-pink-200 text-sm font-semibold">FINANCIAL</div>
      <a href="issue_receipts.php" class="menu-item flex items-center px-5 py-3 text-white">
        <i class="fas fa-receipt w-6"></i>
        <span class="ml-3">Issue Receipts</span>
      </a>
      <a href="reports.php" class="menu-item flex items-center px-5 py-3 text-white">
        <i class="fas fa-chart-bar w-6"></i>
        <span class="ml-3">Reports</span>
      </a>
      
      <div class="px-5 py-3 text-pink-200 text-sm font-semibold">SETTINGS</div>
      <a href="settings.php" class="menu-item flex items-center px-5 py-3 text-white">
        <i class="fas fa-cog w-6"></i>
        <span class="ml-3">Settings</span>
      </a>
      <a href="help.php" class="menu-item flex items-center px-5 py-3 text-white">
        <i class="fas fa-question-circle w-6"></i>
        <span class="ml-3">Help & Support</span>
      </a>
    </nav>
  </div>

  <main class="max-w-6xl mx-auto px-4 py-10">
    <h2 class="text-2xl font-bold mb-6 text-gray-700">Dashboard</h2>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
      <a href="manage_products.php" class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition text-center">
        <h3 class="text-lg font-semibold text-pink-500">Manage Products</h3>
        <p class="text-sm text-gray-500 mt-2">Add, update or remove inventory.</p>
      </a>

      <a href="manage_staff.php" class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition text-center">
        <h3 class="text-lg font-semibold text-pink-500">Manage Staff</h3>
        <p class="text-sm text-gray-500 mt-2">Assign roles & working hours. Add and delete.</p>
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
      
      <a href="appointments.php" class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition text-center">
        <h3 class="text-lg font-semibold text-pink-500">All Appointments</h3>
        <p class="text-sm text-gray-500 mt-2">Complete appointment overview.</p>
      </a>
      
    </div>
  </main>

  <script>
    // Toggle sidebar
    const menuToggle = document.getElementById('menu-toggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    
    menuToggle.addEventListener('click', function(e) {
      e.stopPropagation();
      sidebar.classList.toggle('open');
      overlay.classList.toggle('open');
    });
    
    // Close sidebar when clicking outside
    overlay.addEventListener('click', function() {
      sidebar.classList.remove('open');
      overlay.classList.remove('open');
    });
    
    // Close sidebar when clicking on a menu item
    const menuItems = document.querySelectorAll('.menu-item');
    menuItems.forEach(item => {
      item.addEventListener('click', function() {
        sidebar.classList.remove('open');
        overlay.classList.remove('open');
      });
    });
    
    // Close sidebar with Escape key
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        sidebar.classList.remove('open');
        overlay.classList.remove('open');
      }
    });
  </script>
</body>
</html>
