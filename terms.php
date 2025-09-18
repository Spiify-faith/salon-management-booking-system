<?php
// terms.php
require_once 'auth_helpers.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Terms & Conditions SALONSYNC</title>
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
      <li><a href="services.php" class="hover:text-pink-600">Services</a></li>
      <li><a href="terms.php" class="text-pink-600 underline">Terms</a></li>
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

  <!-- Terms & Conditions Content -->
  <section class="px-6 py-12 max-w-3xl mx-auto">
    <h2 class="text-3xl font-bold text-center text-pink-500 mb-8">Terms & Conditions</h2>
    <div class="bg-white p-6 rounded-2xl shadow-md space-y-6">
      <h3 class="text-xl font-semibold text-pink-600">Introduction</h3>
      <p class="text-gray-700">Welcome to our Salon By using our services, you agree to the following terms and conditions...</p>

      <h3 class="text-xl font-semibold text-pink-600">Booking Policy</h3>
      <p class="text-gray-700">Clients must book at least 24 hours in advance. Cancellations are allowed up to 12 hours before the scheduled appointment...</p>
       
      <h3 class="text-xl font-semibold text-pink-600">Booking fee</h3>
      <p class="text-gray-700">Clients must pay k50,booking fee to secure a slot, note that this amount is non-refundable </p>

      <h3 class="text-xl font-semibold text-pink-600">Payment Policy</h3>
      <p class="text-gray-700">We accept payment via Airtel/Zamtel/MTN Mobile Money and Visa/Credit Cards. All appointment booking payments are non-refundable...</p>

      <h3 class="text-xl font-semibold text-pink-600">Liability</h3>
      <p class="text-gray-700">We are not responsible for any allergic reactions or other issues arising from the use of our products or services, please read ingredients and inquire where not clear...</p>

      <h3 class="text-xl font-semibold text-pink-600">Privacy Policy</h3>
      <p class="text-gray-700">We value your privacy. We will not share your personal information with third parties without your consent...</p>
    </div>
  </section>

  <!-- Footer -->
  <footer class="bg-white text-center text-sm text-gray-500 py-4">
    &copy; 2025 SALONSYNC. All rights reserved.
  </footer>
</body>
</html>