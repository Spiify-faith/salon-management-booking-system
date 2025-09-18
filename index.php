<?php
// index.php
require_once 'auth_helpers.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Home SALONSYNC</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-pink-50 text-gray-800">
  <!-- Navbar -->
  <nav class="bg-white shadow-md py-4 px-6 flex justify-between items-center">
    <h1 class="text-2xl font-bold text-pink-500">SALONSYNC</h1>
    <ul class="flex space-x-4 text-sm font-medium">
      <li><a href="index.php" class="text-pink-600 hover:underline">Home</a></li>
      <li><a href="booking.php" class="hover:text-pink-600">Booking</a></li>
      <li><a href="products.php" class="hover:text-pink-600">Products</a></li>
      <li><a href="services.php" class="hover:text-pink-600">Services</a></li>
      <li><a href="terms.php" class="hover:text-pink-600">Terms</a></li>
      <?php if (isLoggedIn()): ?>
        <li>
          <form action="logout.php" method="post">
            <button type="submit" class="hover:text-pink-600">Logout</button>
          </form>
        </li>
      <?php else: ?>
        <li><a href="login.php" class="hover:text-pink-600">Login</a></li>
      <?php endif; ?>
    </ul>
  </nav>

  <!-- Hero Banner -->
  <section class="text-center py-16 bg-pink-200">
    <h2 class="text-4xl font-bold text-pink-800 mb-4">Pamper Yourself With The Best</h2>
    <p class="text-lg text-pink-900 mb-6">Hair, Nails, Lashes and a glam experiences that leave you glowing.</p>
    <a href="booking.php" class="bg-pink-500 text-white px-6 py-2 rounded-xl text-sm hover:bg-pink-600 transition">
      Book an Appointment
    </a>
  </section>

  <!-- Promotions Section -->
  <section class="px-8 py-16">
    <h3 class="text-2xl font-bold text-center text-pink-600 mb-8">Hot Promos</h3>
    <div class="grid gap-6 md:grid-cols-3">
      <div class="bg-white rounded-xl shadow-md p-4 text-center">
        <h4 class="font-semibold text-lg text-pink-500">20% Off First Booking</h4>
        <p class="text-sm mt-2">New clients get an exclusive discount on any service.</p>
      </div>
      <div class="bg-white rounded-xl shadow-md p-4 text-center">
        <h4 class="font-semibold text-lg text-pink-500">Loyalty Rewards</h4>
        <p class="text-sm mt-2">Collect points every visit and redeem for free services.</p>
      </div>
      <div class="bg-white rounded-xl shadow-md p-4 text-center">
        <h4 class="font-semibold text-lg text-pink-500">Refer & Glow</h4>
        <p class="text-sm mt-2">Refer a friend and both get 10% off your next visit!</p>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="bg-white text-center text-sm text-gray-500 py-4">
    &copy; 2025 SALON & E-COMMERCE. All rights reserved.
  </footer>
</body>
</html>
