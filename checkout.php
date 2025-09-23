<?php
// checkout.php
require_once 'auth_helpers.php';
// Check if user is logged in for checkout page
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cart = $_SESSION['cart'] ?? [];
    $payment = $_POST['payment_method'] ?? '';

    if (empty($cart) || !$payment) {
        header("Location: cart.php");
        exit;
    }

    // Save order
    $total = 0;
    foreach ($cart as $item) {
        $total += $item['price'] * $item['quantity'];
    }

    $user_id = $_SESSION['user_id'];
    $conn->query("INSERT INTO orders (user_id, payment_method, total_amount, created_at) VALUES ($user_id, '$payment', $total, NOW())");
    $order_id = $conn->insert_id;

    // Save each item + update stock
    foreach ($cart as $item) {
        $id = $item['id'];
        $qty = $item['quantity'];
        $price = $item['price'];

        $conn->query("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES ($order_id, $id, $qty, $price)");
        $conn->query("UPDATE products SET stock = stock - $qty WHERE id = $id");
    }

    // Store last order ID to show in receipt
    $_SESSION['last_order_id'] = $order_id;
    unset($_SESSION['cart']);

    header("Location: receipt.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Checkout - SalonSync</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-pink-50 text-gray-800 p-8">
  <div class="max-w-2xl mx-auto">
    <h1 class="text-3xl font-bold text-pink-500 mb-6">Checkout</h1>
    
    <form method="post" action="checkout.php" class="bg-white p-6 rounded-xl shadow">
      <h3 class="text-lg font-semibold text-gray-800 mb-4">Choose Payment Method</h3>
      <div class="mb-4">
        <select name="payment_method" required class="w-full border p-2 rounded">
          <option value="">-- Select Payment --</option>
          <option value="cash">Cash on Delivery</option>
          <option value="mobile">Mobile Money</option>
        </select>
      </div>
      
      <button type="submit" class="bg-pink-500 text-white px-6 py-2 rounded hover:bg-pink-600">
        Complete Purchase
      </button>
    </form>
    
    <div class="mt-6">
      <a href="cart.php" class="text-pink-500 hover:underline">← Back to cart</a>
    </div>
  </div>
</body>
</html>