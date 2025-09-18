<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['product_id'];
    $name = $_POST['name'];
    $price = (float) $_POST['price'];
    $stock = (int) $_POST['stock'];
    $quantity = (int) $_POST['quantity'];

    if ($quantity < 1 || $quantity > $stock) {
        // Invalid quantity
        header('Location: products.php?error=invalid_quantity');
        exit;
    }

    // Initialize cart
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $found = false;
    // Check if product is already in cart
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id'] == $id) {
            $item['quantity'] += $quantity;

            // Enforce stock limit
            if ($item['quantity'] > $stock) {
                $item['quantity'] = $stock;
            }

            $found = true;
            break;
        }
    }

    // If not found, add new entry
    if (!$found) {
        $_SESSION['cart'][] = [
            'id' => $id,
            'name' => $name,
            'price' => $price,
            'quantity' => $quantity,
            'stock' => $stock
        ];
    }

    header('Location: cart.php');
    exit;
}
