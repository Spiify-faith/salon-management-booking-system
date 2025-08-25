<?php
session_start();
require_once 'db.php'; // Adjust if your DB file has a different name

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

    $conn->query("INSERT INTO orders (payment_method, total_amount, created_at) VALUES ('$payment', $total, NOW())");
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




<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// If form not submitted properly
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['payment_method'])) {
    echo "Invalid request.";
    exit;
}

$paymentMethod = $_POST['payment_method'];
$cart = $_SESSION['cart'] ?? [];

if (empty($cart)) {
    echo "Your cart is empty.";
    exit;
}

// (Optional) Save the order to session as 'orders' (later you can save to database)
$_SESSION['orders'][] = [
    'items' => $cart,
    'payment_method' => $paymentMethod,
    'date' => date('Y-m-d H:i:s')
];

// Clear the cart
$_SESSION['cart'] = [];

?>

<!DOCTYPE html>
<html>
<head>
  <title>Order Confirmed - SalonSync</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-green-50 text-gray-800 p-8">
  <div class="bg-white p-6 rounded-xl shadow max-w-xl mx-auto">
    <h1 class="text-3xl font-bold text-green-600 mb-4">✅ Order Confirmed</h1>
    <p class="mb-2">Thank you for your purchase using <strong><?= htmlspecialchars($paymentMethod) ?></strong>.</p>
    <a href="products.php" class="text-pink-600 underline">← Continue shopping</a>
  </div>
</body>
</html>
