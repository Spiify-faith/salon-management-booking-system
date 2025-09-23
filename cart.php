<?php
session_start();
require_once 'auth_helpers.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$cart = $_SESSION['cart'] ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Your Cart - SalonSync</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<nav class="bg-white shadow-md py-4 px-6 flex justify-between items-center">
  <h1 class="text-2xl font-bold text-pink-500">SALONSYNC</h1>
  <ul class="flex space-x-4 text-sm font-medium">
    <li><a href="index.php" class="hover:text-pink-600">Home</a></li>
    <li><a href="booking.php" class="hover:text-pink-600">Booking</a></li>
    <li><a href="products.php" class="text-pink-600 underline">Products</a></li>
    <li><a href="services.php" class="hover:text-pink-600">Services</a></li>
    <li><a href="terms.php" class="hover:text-pink-600">Terms</a></li>
</nav>

<body class="bg-pink-50 text-gray-800 p-8">
       
  <h1 class="text-3xl font-bold text-pink-500 mb-6">Your Cart</h1>

  <?php if (empty($cart)): ?>
   <p>Your cart is empty.</p>

  <form method="post" action="checkout.php" class="mt-8 bg-white p-6 rounded-xl shadow max-w-md">
    <h3 class="text-lg font-semibold text-gray-800 mb-2">Choose Payment Method</h3>
    <select name="payment_method" required class="w-full border p-2 rounded mb-4">
      <option value="">-- Select Payment --</option>
      <option value="cash">Cash on Delivery</option>
      <option value="mobile">Mobile Money</option>
    </select>
    <button type="submit" class="bg-pink-500 text-white px-6 py-2 rounded hover:bg-pink-600">Checkout</button>
  </form>

  <?php else: ?>
    <div class="space-y-4">
      <?php
      $total = 0;
      foreach ($cart as $item):
        $itemTotal = $item['price'] * $item['quantity'];
        $total += $itemTotal;
      ?>
        <div class="p-4 bg-white shadow rounded-xl">
          <h2 class="font-semibold"><?= htmlspecialchars($item['name']) ?></h2>
          <p>Price: K<?= number_format($item['price'], 2) ?></p>
          <p>Quantity: <?= $item['quantity'] ?> (Max: <?= $item['stock'] ?>)</p>
          <p>Total: K<?= number_format($itemTotal, 2) ?></p>

          <!-- Remove from cart form -->
          <form method="post" action="remove_from_cart.php" class="mt-2">
            <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
            <button class="text-red-500 underline text-sm" type="submit">Remove</button>
          </form>
        </div>
      <?php endforeach; ?>
    </div>

    <h2 class="mt-6 text-xl font-bold">Grand Total: K<?= number_format($total, 2) ?></h2>

    <!-- ✅ Checkout form -->
    <form method="post" action="checkout.php" class="mt-8 bg-white p-6 rounded-xl shadow max-w-md">
      <h3 class="text-lg font-semibold mb-2">Choose Payment Method</h3>
      <select name="payment_method" required class="w-full border p-2 rounded mb-4">
        <option value="cash">Cash on Delivery</option>
        <option value="mobile">Mobile Money</option>
      </select>
      <button type="submit" class="bg-green-500 text-white px-6 py-2 rounded hover:bg-green-600 w-full">
        Confirm & Checkout
      </button>
    </form>
  <?php endif; ?>

</body>
</html>

