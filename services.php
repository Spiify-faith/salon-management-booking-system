<?php
// services.php
require_once 'auth_helpers.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Services SALONSYNC</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-pink-50 text-gray-800">
  <!-- Navbar -->
  <nav class="bg-white shadow-md py-4 px-6 flex justify-between items-center">
    <h1 class="text-2xl font-bold text-pink-500">SALONSYNC</h1>
    <ul class="flex space-x-4 text-sm font-medium">
      <li><a href="index.php" class="hover:text-pink-600">Home</a></li>
      <li><a href="booking.php" class="hover:text-pink-600">Booking</a></li>
      <li><a href="products.php" class="hover:text-pink-600">Products</a></li>
      <li><a href="services.php" class="text-pink-600 underline">Services</a></li>
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

  <!-- Services Gallery -->
  <section class="px-6 py-12 max-w-6xl mx-auto">
    <h2 class="text-3xl font-bold text-center text-pink-500 mb-8">Our Services</h2>
    <div class="grid gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
      <!-- Service Example Card -->
      <div class="bg-white rounded-2xl shadow-md overflow-hidden">
        <img src="uploads/acrylic.jpg" alt="Service 1" class="w-full h-48 object-cover">
        <div class="p-4">
          <h3 class="text-lg font-semibold text-pink-600">Acrylic Nails - Long</h3>
          <p class="text-sm text-gray-600">Bold, beautiful long acrylics. Customize on booking.</p>
        </div>
      </div>

      <div class="bg-white rounded-2xl shadow-md overflow-hidden">
        <img src="uploads/micro braids.jpg" alt="Service 2" class="w-full h-48 object-cover">
        <div class="p-4">
          <h3 class="text-lg font-semibold text-pink-600">Micro Braids</h3>
          <p class="text-sm text-gray-600">Can last you up to 2 months without looking old!</p>
        </div>
      </div>

      <div class="bg-white rounded-2xl shadow-md overflow-hidden">
        <img src="uploads/gel nails.webp" alt="Service 3" class="w-full h-48 object-cover">
        <div class="p-4">
          <h3 class="text-lg font-semibold text-pink-600">Gel Polish</h3>
          <p class="text-sm text-gray-600">High-gloss, chip-resistant finish. Add design in notes!</p>
        </div>
      </div>

      <div class="bg-white rounded-2xl shadow-md overflow-hidden">
        <img src="uploads/hair colouring.jpg" alt="Service 4" class="w-full h-48 object-cover">
        <div class="p-4">
          <h3 class="text-lg font-semibold text-pink-600">Hair Coloring</h3>
          <p class="text-sm text-gray-600">Professional hair coloring with premium products.</p>
        </div>
      </div>

      <div class="bg-white rounded-2xl shadow-md overflow-hidden">
        <img src="uploads/facial.jpg" alt="Service 5" class="w-full h-48 object-cover">
        <div class="p-4">
          <h3 class="text-lg font-semibold text-pink-600">Facial Treatments</h3>
          <p class="text-sm text-gray-600">Rejuvenating facials for glowing skin.</p>
        </div>
      </div>

      <div class="bg-white rounded-2xl shadow-md overflow-hidden">
        <img src="uploads/lashes.webp" alt="Service 6" class="w-full h-48 object-cover">
        <div class="p-4">
          <h3 class="text-lg font-semibold text-pink-600">Eyelash Extensions</h3>
          <p class="text-sm text-gray-600">Luxurious eyelash extensions for a dramatic look.</p>
        </div>
      </div>
    </div>
    
  </section>

  <!-- Footer -->
  <footer class="bg-white text-center text-sm text-gray-500 py-4">
    &copy; 2025 SALONSYNC. All rights reserved.
  </footer>
</body>
</html>